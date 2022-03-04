<?php


namespace App\Marketplace\Payment;

use App\Marketplace\Utility\MoneroConvert;
use App\Marketplace\Utility\MoneroRPC\walletRPC;

class MoneroPayment implements Coin
{
    /**
     * Instance of Monero RPC
     * @var
     */
    protected $monero;

    public function __construct() {
        $this->monero = new walletRPC([
            'host' => config('coins.monero.host'),
            'port' => config('coins.monero.port'),
            'user' => config('coins.monero.username'),
            'password' => config('coins.monero.password')
        ]);
    }

    public function generateAddress(array $parameters = []): string {
        if (array_key_exists('payment_id', $parameters)) {
            $response = $this->monero->make_integrated_address($parameters['payment_id']);
        } else {
            $response = $this->monero->make_integrated_address();
        }
        return $response['integrated_address'];
    }




    /**
     * Get received balance by PaymentID in piconero
     *
     * @param array $parameters
     * @return float
     */
    public function getBalance(array $parameters = []): float {
        if (!array_key_exists('address', $parameters))
            throw new \Exception('Parameter address is required');

        $split_address = $this->getPaymentId($parameters['address']);

        $paymentId = $split_address['payment_id'];
        $standard_address = $split_address['standard_address'];

        $payments = $this->monero->get_payments($paymentId);
        if (empty($payments))
            return 0;
        $total_received = 0.0;
        foreach ($payments['payments'] as $payment) {
            $total_received += $payment['amount'];
        }

        $tx = $payments['payments'][0]['tx_hash'];
        // dd($payments);
        // dd($this -> monero -> get_transfer_by_txid($tx));
        // dd($this->monero->getbalance());
        // dd($parameters['address']);
        // dd($payments);

        // $wallet_balance = $this->monero->getbalance();
        // $per_subaddress = $wallet_balance['per_subaddress'];
        // foreach($per_subaddress as $subaddress)
        // {
        //     $adrs = $subaddress['address'];

        //     if($adrs == $standard_address)
        //     {
        //         return $subaddress['balance']  / 1000000000000;
        //     }
        // }

        return $total_received / 1000000000000;
    }

    /**
     *
     *
     * @param string $toAddress
     * @param float $amount Monero amount
     * @return mixed|void
     */
    public function sendToAddress(string $toAddress, float $amount) {
        $tx = $this->monero->transfer(['address' => $toAddress, 'amount' => $amount, 'priority' => 1]);
        return $tx;
    }

    /**
     * Returns paymentID from integrated address
     *
     * @param string $address
     * @return string
     * @throws \Exception
     */
    public function getPaymentId(string $address): array {
        if ($address == '' || $address == null)
            throw new \Exception('Address is required');
        $res = $this->monero->split_integrated_address($address);
        // return $res['payment_id'];
        return $res;
    }

    /**
     * Send transaction to many addresses
     *
     * $addressesAmounts is in format ['address' => amount]
     * EXAMPLE:
     * ['amount' => 1, 'address' => '9sZABNdyWspcpsCPma1eUD5yM3efTHfsiCx3qB8RDYH9UFST4aj34s5Yg', 'amount' => 2, 'address' => 'BhASuWq4HcBL1KAwt4wMBDhkpwsqgz9EBY66g5UBrueRFLCESojoaHaTPsjh']
     *
     *
     * @param string $fromAccount
     * @param array $addressesAmounts
     * @return object
     * @throws \Exception
     */
    function sendToMany(array $addressesAmounts) {
        // dd($addressesAmounts);
        $destinations = [];
        $firstTx = null;
        foreach ($addressesAmounts as $address => $amount) {
            $item = [
                "amount" => $amount,
                "address" => $address
            ];
            array_push($destinations, $item);
        }

        $params = [
            'destinations' => $destinations
        ];
        // $tx = $this->monero->transfer_split($amount, $address); // Multiple payments in one transaction
        $tx = $this->monero->transfer_split($params); // Multiple payments in one transaction
        // remember only firs
        // if(is_null($firstTx)){
        //     $firstTx = $tx;
        // }
        return $tx;
    }

    function usdToCoin($usd): float
    {
        // return round(MoneroConvert::usdToXmr($usd), 12, PHP_ROUND_HALF_DOWN);
        return round(MoneroConvert::usdToXmr($usd), 8, PHP_ROUND_HALF_DOWN);
    }

    /**
     * Return the label of the monero
     *
     * @return string
     */
    function coinLabel(): string
    {
        return 'xmr';
    }


}