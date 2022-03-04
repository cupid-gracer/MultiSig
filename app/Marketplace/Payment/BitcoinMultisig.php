<?php

namespace App\Marketplace\Payment;


use App\Marketplace\Utility\RPCWrapper;
use App\Marketplace\Utility\BitcoinConverter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
// use Carbon\Carbon;

class BitcoinMultisig implements Coin
{

    /**
     * RPCWrapper Server instance
     *
     * @var
     */
    protected $bitcoind;

    /**
     * RPCWrapper constructor.
     */
    public function __construct()
    {
        $this -> bitcoind = new RPCWrapper(config('coins.bitcoin.username'),
            config('coins.bitcoin.password'),
            config('coins.bitcoin.host'),
            config('coins.bitcoin.port'));
    }

    /**
     * Generate new address with optional btc user parameter
     *
     * @param array $params
     * @return string
     * @throws \Exception
     */
    function generateAddress(array $params = []): string
    {
        // only if the btc user is set then call with parameter
        if(array_key_exists('btc_user', $params))
            $address = $this -> bitcoind -> getnewaddress($params['btc_user']);
        else
            $address = $this -> bitcoind -> getnewaddress();

        // Error in bitcoin
        if($this -> bitcoind -> error)

            throw new \Exception($this -> bitcoind -> error);

        return $address;
    }

    /**
     * Create new multisig address 
     *
     * @param array $params
     * @return string
     * @throws \Exception
     */
    function createMultisigAddress(array $params = []):array
    {
        // create mutisig 2 of 3 address
        $result = $this -> bitcoind -> createmultisig(2, $params);

        // Error in bitcoin
        if($this -> bitcoind -> error){
            throw new \Exception($this -> bitcoind -> error);
        }
        return $result;
    }

    /**
     * Add new multisig address 
     *
     * @param array $params
     * @return string
     * @throws \Exception
     */
    function addMultisigAddress(array $params = []):array
    {
        // add mutisig 2 of 3 address
        $result = $this -> bitcoind -> addmultisigaddress(2, $params);

        // Error in bitcoin
        if($this -> bitcoind -> error){
            throw new \Exception($this -> bitcoind -> error);
        }
        return $result;
    }


    function runImportMulti($address, $timestamp, bool $rescan):bool
    {
        $arg1 = array(array(
            "scriptPubKey" => array( "address" => $address),
            "timestamp" =>$timestamp,
            "watchonly" => true,
            "internal" => false
        ));
        $arg2 = array("rescan" => $rescan);

        $result = $this -> bitcoind -> importmulti($arg1, $arg2);
        if($result[0]["success"])
        {
            return true;
        }
        return false;
    }


    /**
     * Returns the total received balance of the account
     *
     * @param array $params
     * @return float
     * @throws \Exception
     */
    function getBalance(array $params = []): float
    {
        // $current_timestamp = Carbon::now()->subMinutes(120)->timestamp;
        if(array_key_exists('address', $params)){
            $address = $params['address'];
            $timestamp = $params['timestamp']->timestamp;
            $amount  = $params['amount'];

            if($this -> runImportMulti($address, $timestamp, true))
            {
                $listUnspent = $this -> bitcoind -> listunspent(0, 9999999, array($address));
                foreach ($listUnspent as $unspent){
                    if($unspent['amount'] == $amount)
                    {
        // dd($unspent);
                        return $unspent['amount'];
                    }
                }
                return 0;
            }
            else
            {
                throw new \Exception('You havent specified any parameter');
            } 
        }
        else
            throw new \Exception('You havent specified any parameter');

        if($this -> bitcoind -> error)
            throw new \Exception($this -> bitcoind -> error);

        return 0;
    }

    function getMempoolFee()
    {
        $mempoolinfo = $this -> bitcoind -> getmempoolinfo();
        $minrelaytxfee = $mempoolinfo['minrelaytxfee'];
        return $minrelaytxfee;
    }

    function getRawTx(array $params = []):array
    {
        $address = $params['address'];
        $amount  = $params['amount'];
        // dd($amount);
        $timestamp =$params["timestamp"]->timestamp;
        $redeemScript =$params["redeemScript"];
        $receiversAmounts = $params['receiversAmounts'];
        if($this -> runImportMulti($address, $timestamp, true))
        {
            $listUnspent = $this -> bitcoind -> listunspent(0, 9999999, array($address));
            foreach ($listUnspent as $unspent){
                if($unspent['amount'] == $amount)
                {
                    // dd($unspent);
                    $txid = $unspent['txid'];
                    $vout = $unspent['vout'];
                    $scriptPubKey = $unspent['scriptPubKey'];

                    $arg1 = array(array("txid" => $txid, "vout" => $vout, "scriptPubKey" => $scriptPubKey, "redeemScript" => $redeemScript));
                    $arg2 = array();
                    // dd($receiversAmounts);
                    foreach($receiversAmounts as $address => $amt ){
                        array_push($arg2, array($address => $amt));
                    }
                    $rawTransaction = $this -> bitcoind -> createrawtransaction ($arg1, $arg2);
                    $signRawTransaction = "signrawtransactionwithkey \"".$rawTransaction."\" '[\"YourPrivateKey\"]' '[{\"txid\":\"".$txid."\", \"vout\":".$vout.", \"scriptPubKey\": \"".$scriptPubKey."\", \"redeemScript\": \"".$redeemScript."\"}]'";
                    $result = array(
                                    "vendor_tx" => $signRawTransaction, 
                                    "txid" => $txid,
                                    "vout" => $vout,
                                    "scriptPubKey" => $scriptPubKey
                                   );
                    return $result;
                }
            }
        }
        else
        {
            throw new \Exception('Transaction is not existed!');
        } 

        if($this -> bitcoind -> error)
            throw new \Exception($this -> bitcoind -> error);

        return array();
    }

    function getTrans(string $txid):string
    {
        $transaction = $this -> bitcoind -> gettransaction($txid);
        // dd($transaction);
        if($this -> bitcoind -> error)
            throw new \Exception($this -> bitcoind -> error);

        return "";
    }

    function sendRawTransaction($signedTx)
    {
        $txid = $this -> bitcoind -> sendrawtransaction($signedTx);
        if($this -> bitcoind -> error)
            throw new \Exception($this -> bitcoind -> error);

        return $txid;
    }

    /**
     * Calls a procedure to send from address to address some amount
     *
     * @param string $fromAddress
     * @param string $toAddress
     * @param float $amount
     * @throws \Exception
     */
    function sendToAddress(string $toAddress, float $amount)
    {
        // call bitcoind procedure
        $this -> bitcoind -> sendtoaddress($toAddress, $amount);

        if($this -> bitcoind -> error)
            throw new \Exception("Sending to $toAddress amount $amount \n" . $this -> bitcoind -> error);

    }

    /**
     * Send to array of addresses
     *
     * @param string $fromAccount
     * @param array $addressesAmounts
     * @throws \Exception
     */
    function sendToMany(array $addressesAmounts)
    {
        // send to many addresses
//        foreach ($addressesAmounts as $address => $amount){
//            $this -> bitcoind -> sendtoaddress($address, $amount);
//        }

        $this->bitcoind->sendmany("", $addressesAmounts, (int)config('marketplace.bitcoin.minconfirmations'));


        if ($this->bitcoind->error) {
            $errorString = "";
            foreach ($addressesAmounts as $address => $amount){
                $errorString .= "To $address : $amount \n";
            }
            throw new \Exception( $this->bitcoind->error . "\nSending to: $errorString" );
        }
    }
    /**
     * Convert USD to equivalent BTC amount, rounded on 8 decimals
     *
     * @param $usd
     * @return float
     */
    function usdToCoin($usd): float
    {
        return round( BitcoinConverter::usdToBtc($usd), 8, PHP_ROUND_HALF_DOWN );
    }


    /**
     * Returns the string label of the coin
     *
     * @return string
     */
    function coinLabel(): string
    {
        return 'btc';
    }


}