# Binance API PHP light library with a Docker container

PHP7-based library for handling the most essential Binance API calls.
Binance is one of the biggest cryptocurrency exchanges.

[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.2-8892BF.svg?style=flat-square)](https://php.net/)

## Requirements:

* PHP 7.2+
* NTP (Network Time Protocol Server)

## Installation:

1. Create a Binance API and generate a pair of keys you following the [official Binance instructions](https://support.binance.com/hc/en-us/articles/360002502072-How-to-create-API).
2. Enter your API key in the ```settings.ini``` file.
3. Make sure your server time is synchronised with the NTP network. Install the ```ntp``` package following [their official documentation](https://support.ntp.org/bin/view/Support/InstallingNTP).
Synchronise your server running the corresponding command. For example, if your server is based in Europe use:
```
ntpdate 0.europe.pool.ntp.org
```
4. The ```recvWindow``` parameter in the ```settings.ini``` file specifies the time in milliseconds after which the Binance API
will reject your request if it's not received within this time frame. The default value of 5000 is usually sufficient if your server
is synchronised with ```ntp``` but if you keep getting error responses saying that your request is out of the ```recvWindow``` time frame,
consider incrementing the value of this variable.

## Usage:

Run the code in a PHP environment.

Please refer to the sample API calls in ```examples.php```.

Alternatively, you can run the code in a docker container by using the Docker
setup files in ```docker/```. In order to do this you will need to have
installed [docker-compose](https://docs.docker.com/compose/install/). To run the code in a container
using Nginx, simply run from the ```docker/``` folder: 
```
docker-compose pull
docker-compose up
```

Then you can access the root folder from your local host at:

```
http://127.0.0.1:8080
```
