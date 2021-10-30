<?php

// Copyright (C) 2021 Ivan Stasiuk <brokeyourbike@gmail.com>.
//
// This Source Code Form is subject to the terms of the Mozilla Public
// License, v. 2.0. If a copy of the MPL was not distributed with this file,
// You can obtain one at https://mozilla.org/MPL/2.0/.

namespace BrokeYourBike\ZenithBank;

use Psr\SimpleCache\CacheInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\ClientInterface;
use Carbon\Carbon;
use BrokeYourBike\ZenithBank\Interfaces\TransactionInterface;
use BrokeYourBike\ZenithBank\Interfaces\ConfigInterface;
use BrokeYourBike\ResolveUri\ResolveUriTrait;
use BrokeYourBike\HttpEnums\HttpMethodEnum;
use BrokeYourBike\HttpClient\HttpClientTrait;
use BrokeYourBike\HttpClient\HttpClientInterface;
use BrokeYourBike\HasSourceModel\SourceModelInterface;
use BrokeYourBike\HasSourceModel\HasSourceModelTrait;

/**
 * @author Ivan Stasiuk <brokeyourbike@gmail.com>
 */
class Client implements HttpClientInterface
{
    use HttpClientTrait;
    use ResolveUriTrait;
    use HasSourceModelTrait;

    private ConfigInterface $config;
    private CacheInterface $cache;
    private int $ttlMarginInSeconds = 60;

    public function __construct(ConfigInterface $config, ClientInterface $httpClient, CacheInterface $cache)
    {
        $this->config = $config;
        $this->httpClient = $httpClient;
        $this->cache = $cache;
    }

    public function authTokenCacheKey(): string
    {
        $liveKey = $this->config->isLive() ? 'live' : 'sandbox';
        return __CLASS__ . ':authToken:' . $liveKey;
    }

    public function getAuthToken(): ?string
    {
        if ($this->cache->has($this->authTokenCacheKey())) {
            return (string) $this->cache->get($this->authTokenCacheKey());
        }

        $response = $this->fetchAuthTokenRaw();
        $responseJson = \json_decode((string) $response->getBody(), true);

        if (
            isset($responseJson['tokenDetail']['token']) &&
            is_string($responseJson['tokenDetail']['token']) &&
            isset($responseJson['tokenDetail']['expiration']) &&
            is_string($responseJson['tokenDetail']['expiration'])
        ) {
            $currentTime = Carbon::now();
            $expiresAt = Carbon::parse($responseJson['tokenDetail']['expiration']);

            $this->cache->set(
                $this->authTokenCacheKey(),
                $responseJson['tokenDetail']['token'],
                $expiresAt->subSeconds($this->ttlMarginInSeconds)->diffInSeconds($currentTime)
            );

            return $responseJson['tokenDetail']['token'];
        }

        return null;
    }

    public function fetchAuthTokenRaw(): ResponseInterface
    {
        $options = [
            \GuzzleHttp\RequestOptions::HTTP_ERRORS => false,
            \GuzzleHttp\RequestOptions::HEADERS => [
                'Accept' => 'application/json',
            ],
            \GuzzleHttp\RequestOptions::FORM_PARAMS => [
                'userIdentifyer' => $this->config->getUsername(),
                'userProtector' => $this->config->getPassword(),
            ],
        ];

        $uri = (string) $this->resolveUriFor($this->config->getUrl(), 'api/authentication/getToken');

        return $this->httpClient->request(
            (string) HttpMethodEnum::POST(),
            $uri,
            $options
        );
    }
}
