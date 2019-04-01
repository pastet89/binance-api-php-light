<?php

namespace API;

use API\Exception\BinanceError;

abstract class BinanceCore
{
    const BASE_URL = "https://api.binance.com/api";
    private $recvWindow;
    private $apiKey;
    private $secretKey;
    
    /*
    * @param int    $recvWindow  Max life for the API request in milliseconds.
    *                            According to the Binance API documetation, 
    *                            defaults to 5000.  After the $recvWindow 
    *                            is exceeded, the API will reject the request.
    * @param string $apiKey      Your API key
    * @param string $secretKey   Your secret key
    */
    protected function __construct(int $recvWindow, string $apiKey, string $secretKey)
    {    
        $this->apiKey = $apiKey;
        $this->secretKey = $secretKey;
        $this->recvWindow = $recvWindow;
    }
    
    /*
    * @param   array    $data     Data to be signed with the secret key.
    * @return array               The signed data + an added element 'signature'
    */
    final protected function sign(array $data): array
    {
        $data_str = http_build_query($data);
        $signature = hash_hmac("sha256", $data_str, $this->secretKey);
        $data['signature'] = $signature;
        return $data;
    }
    
    /*
    * @param   array   $data   The data, if any, to which will be added the time markers:
    *                          timestamp and recvWindow.
    * @return array            The result array, to which has been added the time markers.
    */
    final protected function timeMarkers(array $data = []): array
    {
        $timestamp = round(microtime(true) * 1000);
        $timeData = [
            "timestamp" => $timestamp,
            "recvWindow" => $this->recvWindow,
        ];
        return array_merge($data, $timeData);
    }
    
    /*
    * @throws BinanceError
    * @param string $url             The URL of the request to Binance
    * @param array  $data            The data, if any, to be included in the request.
    * @param string $method          The HTTP request method
    * @param bool   $includeAPIKey   Whether to include the API key in the request.
    * @return array                  The parsed associative array from the JSON response
    */
    final protected function APIRequest(
        string $url,
        array $data = [],
        string $method = "GET",
        bool $includeAPIKey = true
    ): array {
        $acceptedMethods = ["POST", "GET", "DELETE"];
        if(!in_array($method, $acceptedMethods)) {
            throw new BinanceError(
                "The accepted request methods are: " . join(", ", $acceptedMethods)
                );
        }
        
        $headers = [];
        if ($includeAPIKey) {
            $headers = ['X-MBX-APIKEY: ' . $this->apiKey];
        }
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        if ($method != "GET") {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        }
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        if ($method == "POST") {
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        $res = curl_exec($curl);
        curl_close($curl);
        $json = json_decode($res, true);
        return self::verifyResponse($json);
    }

    /*
    * @throws BinanceError
    * @param   array   $json  The API response parsed JSON array to be checked for errors.
    * @return array           The verified array.
    */    
    final private function verifyResponse(array $json): array
    {
        if (array_key_exists('msg', $json) and $json['msg'] != null) {
            throw new BinanceError(
                "API response error: " . $json['msg']
            );
        }
        return $json;
    }
}
