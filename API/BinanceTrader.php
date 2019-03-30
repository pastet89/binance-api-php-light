<?php

namespace API;

use API\Exception\BinanceError;

class BinanceTrader extends BinanceCore
{
    /*
    * Constructs the parent abstract class. For the function parameters, check the docstring
    * of __contruct() in the BinanceCore class.
    */    
    public function __construct(...$args)
    {   
        parent::__construct(...$args);
    }

    /*
    * @param string  $currency       The base (first) currency of the market pair
    * @param string  $quoteAsset     The quote (second) currency of the market pair
    * @return array                  Array with the order book entries
    */
    public static function getOrderBook(
        string $currency,
        string $quoteAsset = "BTC"
    ): array {
        $data = [
            "symbol" => $currency . $quoteAsset
        ];
        $url = self::BASE_URL . "/v1/depth?" . http_build_query($data);
        $order_book = self::APIRequest($url, [], "GET", false);
        return $order_book;
    }
    
    /*
    * @throws BinanceError
    * @param   string $currency        The base (first) currency of the market pair
    * @param   array  $quoteAsset      The quote (second) currency of the market pair
    * @param   int    $limit           The number of trades to be returned (1 to 1000)
    * @return array                    Array with the historical trades
    */
    public static function historicalTrades(
        string $currency, 
        string $quoteAsset,
        int $limit = 500): array {
        if (!is_numeric($limit) or $limit > 1000 or $limit < 0) {
            throw new BinanceError("The acceptable limit range is from 1 to 1000.");
        }
        $data = [
            "symbol" => $currency . $quoteAsset,
            "limit" => $limit
        ];
        $url = self::BASE_URL . "/v1/depth?" . http_build_query($data);
        return self::APIRequest($url, [], "GET", false);
    }
    
    /*
    * @throws BinanceError
    * @param   string $targetAsset     The requested currency
    * @return float                    The balance of the requested currency
    */
    public function getBalance(string $targetAsset = "BTC"): float
    {
        foreach ($this->getBalances() as $asset => $balanceData) {
            if ($balanceData ['asset'] == $targetAsset) {
                return $balanceData['balance'];
            }
        }
        throw new BinanceError("Non-existing currency!");
    }

    /*
    * @yield array                  The balance data for all currencies
    */
    public function getBalances(): iterable
    {
        $url = self::BASE_URL . "/v3/account?";
        $url .= http_build_query($this->sign($this->timeMarkers()));
        $account = $this->APIRequest($url);
        $balances = $account['balances'];
        foreach ((array) $balances as $balance) {
            yield [
                "asset" => $balance['asset'],
                "balance" => $balance['free']
            ];
        }
    }

    /*
    * @throws BinanceError
    * @param   string       $buyOrSell      BUY or SELL?
    * @param   float        $quantity       Amount of the currency we want to sell or buy
    * @param   string       $currency       The base (first) currency of the market pair
    * @param   float        $price          The price (valid only if $marketPrice === false)
    * @param   bool         $marketPrice    Whether to execute the order at market price
    * @param   string       $quoteAsset     The quote (second) currency of the market pair
    * @return array                         Array with placed order data
    */
    public function placeOrder(
        string $buyOrSell,
        float $quantity,
        string $currency,
        float $price,
        bool $marketPrice = false,
        string $quoteAsset="BTC"
    ): array {
        $url = self::BASE_URL . "/v3/order?";
        $symbol = $currency . $quoteAsset;
        $price = number_format($price, 8);
        $orderType = strtoupper($orderType);
        $orderTypes = ["BUY", "SELL"];
        if(!in_array($buyOrSell, $orderTypes)) {
            throw new BinanceError("The accepted order types are: SELL, BUY");
        }
        $data = [
            "symbol" => $symbol, 
            "side" => $buyOrSell
        ];
        if ($marketPrice === true) {
            $data['type'] = "MARKET";
            $data['quantity'] = $quantity;
        } else {
            $data['type'] = "LIMIT";
            $data['quantity'] = $quantity;        
            $data['price'] = $price;        
            $data['timeInForce'] = "GTC";        
        }
        $data = $this->sign($this->timeMarkers($data));
        return $this->APIRequest($url, $data, "POST");
    }

    /*
    * @param   int          $orderId        The order id
    * @param   string       $currency       The base (first) currency of the market pair
    * @param   string       $quoteAsset     The quote (second) currency of the market pair
    * @return array                         The API response
    */
    public function orderStatus(int $orderId, string $currency, string $quoteAsset="BTC"): array
    {
        return $this->manageOrder($orderId, $currency, $quoteAsset, "GET");
    }

    /*
    * @param   int          $orderId        The order id
    * @param   string       $currency       The base (first) currency of the market pair
    * @param   string       $quoteAsset     The quote (second) currency of the market pair
    * @return array                         The API response
    */
    public function cancelOrder(int $orderId, string $currency, string $quoteAsset="BTC"): array
    {
        return $this->manageOrder($orderId, $currency, $quoteAsset, "DELETE");
    }   

    /*
    * @param   int          $orderId        The order id
    * @param   string       $currency       The base (first) currency of the market pair
    * @param   string       $quoteAsset     The quote (second) currency of the market pair
    * @param   string       $method         The HTTP request method
    * @return array                         The API response array
    */
    private function manageOrder(
        int $orderId,
        string $currency, 
        string $quoteAsset="BTC", 
        string $method
    ): array {
        $url = self::BASE_URL . "/v3/order?";
        $data = [
            "symbol" => $currency . $quoteAsset,
            "orderId" => $orderId
        ];
        $url .= http_build_query($this->sign($this->timeMarkers($data)));
        return $this->APIRequest($url, [], $method);
    }
}
