<?php

require 'config.php';
require 'autoload.php';

use API\BinanceTrader;

/*
* I. Static methods (can be executed also as instance methods):
* do not require API keys.
*/

# 1. Order book

$orderBook = BinanceTrader::getOrderBook("ETH", "BTC");

# 2. Historical trades

$historicalTrades = BinanceTrader::historicalTrades("ETH", "BTC");


/*
* II. Instance methods:
* require API keys.
*/

$binance = new BinanceTrader(
    $settings['recvWindow'],
    $settings['apiKey'],
    $settings['secretKey']
);


# 1. Get the free available balance for a specific currency
$balance = $binance->getBalance("ETH");


# 2. Get all available balances

foreach ($binance->getBalances() as $balance) {
    print_r($balance);
}


# 3. Place an order: buy ETH with BTC

$newOrder = $binance->placeOrder("BUY", 1.55, "ETH", 0.04);


# 4. Get order status using order id and a currency pair

$status = $binance->orderStatus(123456, "XRP", "ETH");


# 5. Cancel an order using order id and a currency pair

$status = $binance->cancelOrder(123456, "XRP", "ETH");


