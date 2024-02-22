<?php

// Copyright (C) 2021 Ivan Stasiuk <ivan@stasi.uk>.
//
// This Source Code Form is subject to the terms of the Mozilla Public
// License, v. 2.0. If a copy of the MPL was not distributed with this file,
// You can obtain one at https://mozilla.org/MPL/2.0/.

namespace BrokeYourBike\ZenithBank;

use Psr\SimpleCache\CacheInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\ClientInterface;
use Carbon\Carbon;
use BrokeYourBike\ZenithBank\Models\SendTransactionResponse;
use BrokeYourBike\ZenithBank\Models\FetchTransactionResponse;
use BrokeYourBike\ZenithBank\Models\FetchDomesticTransactionResponse;
use BrokeYourBike\ZenithBank\Models\FetchDomesticAccountResponse;
use BrokeYourBike\ZenithBank\Models\FetchBalanceResponse;
use BrokeYourBike\ZenithBank\Models\FetchAuthTokenResponse;
use BrokeYourBike\ZenithBank\Interfaces\TransactionInterface;
use BrokeYourBike\ZenithBank\Interfaces\ConfigInterface;
use BrokeYourBike\ResolveUri\ResolveUriTrait;
use BrokeYourBike\HttpEnums\HttpMethodEnum;
use BrokeYourBike\HttpClient\HttpClientTrait;
use BrokeYourBike\HttpClient\HttpClientInterface;
use BrokeYourBike\HasSourceModel\SourceModelInterface;
use BrokeYourBike\HasSourceModel\HasSourceModelTrait;
use BrokeYourBike\HasSourceModel\HasSourceModelInterface;

/**
 * @author Ivan Stasiuk <ivan@stasi.uk>
 */
class Client implements HttpClientInterface, HasSourceModelInterface
{
    use HttpClientTrait;
    use ResolveUriTrait;
    use HasSourceModelTrait;

    private ConfigInterface $config;
    private CacheInterface $cache;

    public function __construct(ConfigInterface $config, ClientInterface $httpClient, CacheInterface $cache)
    {
        $this->config = $config;
        $this->httpClient = $httpClient;
        $this->cache = $cache;
    }

    public function getConfig(): ConfigInterface
    {
        return $this->config;
    }

    public function getCache(): CacheInterface
    {
        return $this->cache;
    }

    public function authTokenCacheKey(): string
    {
        return get_class($this) . ':authToken:';
    }

    public function getAuthToken(): string
    {
        if ($this->cache->has($this->authTokenCacheKey())) {
            $cachedToken = $this->cache->get($this->authTokenCacheKey());

            if (is_string($cachedToken)) {
                return $cachedToken;
            }
        }

        $response = $this->fetchAuthTokenRaw();

        $expiresAt = Carbon::parse($response->expiration);

        $this->cache->set(
            $this->authTokenCacheKey(),
            $response->token,
            $expiresAt->diffInSeconds(Carbon::now()) / 2
        );

        return $response->token;
    }

    // GetToken
    public function fetchAuthTokenRaw(): FetchAuthTokenResponse
    {
        $options = [
            \GuzzleHttp\RequestOptions::HEADERS => [
                'Accept' => 'application/json',
            ],
            \GuzzleHttp\RequestOptions::JSON => [
                'userIdentifyer' => $this->config->getUsername(),
                'userProtector' => $this->config->getPassword(),
            ],
        ];

        $uri = (string) $this->resolveUriFor($this->config->getUrl(), 'api/authentication/getToken');

        $response = $this->httpClient->request(
            HttpMethodEnum::POST->value,
            $uri,
            $options
        );

        return new FetchAuthTokenResponse($response);
    }

    // Account Balance Enquiry
    public function fetchBalanceRaw(string $accountNumber): FetchBalanceResponse
    {
        $response = $this->performRequest(HttpMethodEnum::POST, 'api/enquiry/balance', [
            'accountNumber' => $accountNumber,
        ]);

        return new FetchBalanceResponse($response);
    }

    // DOM Account Enquiry
    public function fetchDomesticAccountRaw(string $accountNumber): FetchDomesticAccountResponse
    {
        $response = $this->performRequest(HttpMethodEnum::POST, 'api/enquiry/domAccountEnquiry', [
            'accountNumber' => $accountNumber,
        ]);

        return new FetchDomesticAccountResponse($response);
    }

    // Transaction Enquiry (DOM)
    public function fetchDomesticTransactionRaw(string $reference): FetchDomesticTransactionResponse
    {
        $response = $this->performRequest(HttpMethodEnum::POST, 'api/enquiry/domTransaction', [
            'transactionReference' => $reference,
        ]);

        return new FetchDomesticTransactionResponse($response);
    }

    // Transfer To Zenith (DOM)
    public function sendDomesticTransaction(TransactionInterface $transaction): SendTransactionResponse
    {
        if ($transaction instanceof SourceModelInterface) {
            $this->setSourceModel($transaction);
        }

        $response = $this->performRequest(HttpMethodEnum::POST, 'api/transaction/zenithDomTransfer', [
            'transactionReference' => $transaction->getReference(),
            'paymentReference' => $transaction->getReference(),
            'senderName' => $transaction->getSenderName(),
            'beneficiaryName' => $transaction->getRecipientName(),
            'crAccount' => $transaction->getRecipientAccount(),
            'drAccount' => $transaction->getDebitAccount(),
            'amount' => $transaction->getAmount(),
            'resend' => $transaction->shouldResend(),
        ]);

        return new SendTransactionResponse($response);
    }

    // Transaction Enquiry (NGN)
    public function fetchTransactionRaw(string $reference): FetchTransactionResponse
    {
        $response = $this->performRequest(HttpMethodEnum::POST, 'api/enquiry/transaction', [
            'transactionReference' => $reference,
        ]);

        return new FetchTransactionResponse($response);
    }

    // Transfer To Zenith (NGN)
    public function sendTransaction(TransactionInterface $transaction): SendTransactionResponse
    {
        if ($transaction instanceof SourceModelInterface) {
            $this->setSourceModel($transaction);
        }

        $response = $this->performRequest(HttpMethodEnum::POST, 'api/transaction/zenithTransfer', [
            'amount' => $transaction->getAmount(),
            'bankName' => 'zenith',
            'crAccount' => $transaction->getRecipientAccount(),
            'drAccount' => $transaction->getDebitAccount(),
            'transactionReference' => $transaction->getReference(),
            'description' => $transaction->getReference(),
        ]);

        return new SendTransactionResponse($response);
    }

    // Transfer to Other Bank (NGN)
    public function sendOtherBankTransaction(TransactionInterface $transaction): SendTransactionResponse
    {
        if ($transaction instanceof SourceModelInterface) {
            $this->setSourceModel($transaction);
        }

        $response = $this->performRequest(HttpMethodEnum::POST, 'api/transaction/otherBankTransfer', [
            'amount' => $transaction->getAmount(),
            'bankCode' => $transaction->getRecipientBankCode(),
            'bankName' => 'zenith',
            'crAccount' => $transaction->getRecipientAccount(),
            'drAccount' => $transaction->getDebitAccount(),
            'transactionReference' => $transaction->getReference(),
            'description' => $transaction->getReference(),
        ]);

        return new SendTransactionResponse($response);
    }

    /**
     * @param HttpMethodEnum $method
     * @param string $uri
     * @param array<mixed> $data
     * @return ResponseInterface
     */
    private function performRequest(HttpMethodEnum $method, string $uri, array $data): ResponseInterface
    {
        $options = [
            \GuzzleHttp\RequestOptions::HEADERS => [
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$this->getAuthToken()}",
            ],
            \GuzzleHttp\RequestOptions::JSON => $data,
        ];

        if ($this->getSourceModel()) {
            $options[\BrokeYourBike\HasSourceModel\Enums\RequestOptions::SOURCE_MODEL] = $this->getSourceModel();
        }

        $uri = (string) $this->resolveUriFor($this->config->getUrl(), $uri);
        return $this->httpClient->request($method->value, $uri, $options);
    }
}
