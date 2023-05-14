# zenith-bank-api-client

[![Latest Stable Version](https://img.shields.io/github/v/release/brokeyourbike/zenith-bank-api-client-php)](https://github.com/brokeyourbike/zenith-bank-api-client-php/releases)
[![Total Downloads](https://poser.pugx.org/brokeyourbike/zenith-bank-api-client/downloads)](https://packagist.org/packages/brokeyourbike/zenith-bank-api-client)
[![Maintainability](https://api.codeclimate.com/v1/badges/91df1b66b0b140f8097b/maintainability)](https://codeclimate.com/github/brokeyourbike/zenith-bank-api-client-php/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/91df1b66b0b140f8097b/test_coverage)](https://codeclimate.com/github/brokeyourbike/zenith-bank-api-client-php/test_coverage)

Zenith Bank API Client for PHP

## Installation

```bash
composer require brokeyourbike/zenith-bank-api-client
```

## Usage

```php
use BrokeYourBike\ZenithBank\Client;
use BrokeYourBike\ZenithBank\Interfaces\ConfigInterface;

assert($config instanceof ConfigInterface);
assert($httpClient instanceof \GuzzleHttp\ClientInterface);
assert($psrCache instanceof \Psr\SimpleCache\CacheInterface);

$apiClient = new Client($config, $httpClient, $psrCache);
$apiClient->fetchAuthTokenRaw();
```

## Authors
- [Ivan Stasiuk](https://github.com/brokeyourbike) | [Twitter](https://twitter.com/brokeyourbike) | [LinkedIn](https://www.linkedin.com/in/brokeyourbike) | [stasi.uk](https://stasi.uk)

## License
[Mozilla Public License v2.0](https://github.com/brokeyourbike/zenith-bank-api-client-php/blob/main/LICENSE)
