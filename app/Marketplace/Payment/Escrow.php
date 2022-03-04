<?php


namespace App\Marketplace\Payment;


use App\Marketplace\Utility\FeeCalculator;
use App\Purchase;
use Illuminate\Support\Facades\Log;

class Escrow extends Payment
{

    /**
     * Procedure when the purchase is created
     *
     * @throws \Exception
     */
    function purchased()
    {
        // generate multisig address
        if($this -> purchase -> type == "multisig")
        {
            $pubkeys = $this -> purchase -> getBuyerPubkey();
            $cma = $this->coin->createMultisigAddress($pubkeys);
            $ama = $this->coin->addMultisigAddress($pubkeys);
            $this->purchase->address = $cma['address'];
            $this->purchase->multisig_address = $ama['address'];
            $this->purchase->redeem_script = $cma['redeemScript'];
            // dd($result);
        }
        else
        {
            // generate escrow address as the account pass the Purchase id
            $this->purchase->address = $this->coin->generateAddress(['user' => $this->purchase->id]);
        }
    }

    /**
     * Empty procedure for sent
     */
    function sent()
    {
    }

    /**
     * Get Tx Id for multisig
     */
    function rawTx($receiving_address = array())
    {
        $isDispute = false;
        if(count($receiving_address) > 0)
            $isDispute = true;
        $mempoolfee = $this->coin->getMempoolFee();
        return $this->coin->getRawTx(['address' => $this -> purchase -> address, 'timestamp' => $this -> purchase -> created_at, 'amount' => $this -> purchase -> to_pay, 'receiversAmounts' => $isDispute ? $receiving_address : $this->receiversAmounts($mempoolfee), 'redeemScript' => $this -> purchase -> redeem_script]);
    }

    function sendRawTransaction($tx)
    {
        return $this->coin->sendRawTransaction($tx);
    }

    /**
     * Release funds to the vendor
     */
    function delivered()
    {
        // call a coin procedure to send funds
        $this->coin->sendToMany($this->receiversAmounts());

    }

    function receiversAmounts($fee = 0)
    {
        // fee that needs to be caluclated
        $feeCaluclator = new FeeCalculator($this->purchase->to_pay - $fee);

        // make array of receivers
        $receiversAmounts = [
            // vendor receiver
            $this->purchase->vendor->user-> coinAddress($this -> coinLabel()) -> address
                => $feeCaluclator->getBase(),
        ];

        // check if user has refered user
        $hasReferral = $this -> purchase -> buyer -> hasReferredBy();

        // set the buyer's referred by user into receivers
        if($hasReferral){
            $referredByUserAddress = $this -> purchase -> buyer -> referredBy -> coinAddress($this -> coinLabel()) -> address;

            $receiversAmounts[$referredByUserAddress] = $feeCaluclator -> getFee($hasReferral);
        }


        // send the funds to the random address of the market
        $marketplaceAddresses = config('coins.market_addresses.' . $this -> coinLabel());
        if (!empty($marketplaceAddresses)) {
            $randomMarketAddress = $marketplaceAddresses[array_rand($marketplaceAddresses)];
            $receiversAmounts[$randomMarketAddress] = $feeCaluclator->getFee($hasReferral);
        }

        // dd($receiversAmounts);

        return $receiversAmounts;
    }

    /**
     * Resolve by sending funds to passed address
     *
     * @param array $parameters
     */
    function resolved(array $parameters)
    {

        if (!array_key_exists('receiving_address', $parameters))
            throw new \Exception('There is no receiving address defined!');

        $mempoolfee = 0;

        if($this -> purchase -> type == "multisig")
            $mempoolfee = $this->coin->getMempoolFee();

        // calculate fee
        $feeCaluclator = new FeeCalculator($this -> purchase -> to_pay - $mempoolfee);

        // make array of receivers
        $receiversAmounts = [
            $parameters['receiving_address'] => $feeCaluclator->getBase(),
        ];

        // send the funds to the random address
        $marketplaceAddresses = config('coins.market_addresses.' . $this -> coinLabel());
        if (!empty($marketplaceAddresses)) {
            // set the market address as a receiver
            $randomMarketAddress = $marketplaceAddresses[array_rand($marketplaceAddresses)];


            $receiversAmounts[$randomMarketAddress] = $feeCaluclator->getFee();
        }
        if($this -> purchase -> type == "multisig")
            return $receiversAmounts;
        // call a coin procedure to send funds
        $this->coin->sendToMany($receiversAmounts);

    }

    /**
     * Returns balance of the purchase's address
     *
     * @return float
     * @throws \Exception
     */
    function balance(): float
    {
        return $this->coin->getBalance(['account' => $this->purchase->id, 'address' => $this -> purchase -> address, 'timestamp' => $this -> purchase -> created_at, 'amount' => $this -> purchase -> to_pay]);
    }

    /**
     * Convert to amount of coin
     *
     * @param $usd
     * @return float
     */
    function usdToCoin($usd): float
    {
        return $this -> coin ->usdToCoin($usd);
    }

    /**
     * Return Coin's label
     *
     * @return string
     */
    function coinLabel(): string
    {
        return $this -> coin -> coinLabel();
    }

    /**
     * Procedure when the purchase is canceled
     *
     * @throws \Exception
     */
    public function canceled()
    {
        // if there is balance on the address
        if(($balanceAddres = $this->balance()) >0){
            // fee that needs to be caluclated
            $feeCaluclator = new FeeCalculator($balanceAddres);

            // make array of receivers
            $receiversAmounts = [
                // buyer receiver
                $this->purchase->buyer-> coinAddress($this -> coinLabel()) -> address
                    => $feeCaluclator->getBase(),
            ];

            // check if user has refered user
            $hasReferral = false; // no referal on canceled purchases


            // send the funds to the random address of the market
            $marketplaceAddresses = config('coins.market_addresses.' . $this -> coinLabel());
            if (!empty($marketplaceAddresses)) {
                $randomMarketAddress = $marketplaceAddresses[array_rand($marketplaceAddresses)];
                $receiversAmounts[$randomMarketAddress] = $feeCaluclator->getFee($hasReferral);
            }

            // call a coin procedure to send funds to a buyer and to market
            $this->coin->sendToMany($receiversAmounts);

        }

    }


}