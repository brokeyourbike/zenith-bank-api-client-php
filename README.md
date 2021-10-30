# zenith-bank-api-client

[![Latest Stable Version](https://img.shields.io/github/v/release/brokeyourbike/zenith-bank-api-client-php)](https://github.com/brokeyourbike/zenith-bank-api-client-php/releases)
[![Total Downloads](https://poser.pugx.org/brokeyourbike/zenith-bank-api-client/downloads)](https://packagist.org/packages/brokeyourbike/zenith-bank-api-client)
[![License: MPL-2.0](https://img.shields.io/badge/license-MPL--2.0-purple.svg)](https://github.com/brokeyourbike/zenith-bank-api-client-php/blob/main/LICENSE)

[![Maintainability](https://api.codeclimate.com/v1/badges/91df1b66b0b140f8097b/maintainability)](https://codeclimate.com/github/brokeyourbike/zenith-bank-api-client-php/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/91df1b66b0b140f8097b/test_coverage)](https://codeclimate.com/github/brokeyourbike/zenith-bank-api-client-php/test_coverage)
[![tests](https://github.com/brokeyourbike/zenith-bank-api-client-php/actions/workflows/tests.yml/badge.svg)](https://github.com/brokeyourbike/zenith-bank-api-client-php/actions/workflows/tests.yml)

Zenith Bank API Client for PHP

## Installation

```bash
composer require brokeyourbike/zenith-bank-api-client
```

## Usage

```php
use BrokeYourBike\ZenithBank\Client;

$apiClient = new Client($config, $httpClient, $psrCache);
$apiClient->fetchAuthTokenRaw();
```

## License
[Mozilla Public License v2.0](https://github.com/brokeyourbike/zenith-bank-api-client-php/blob/main/LICENSE)
