<?php

// Copyright (C) 2021 Ivan Stasiuk <brokeyourbike@gmail.com>.
//
// This Source Code Form is subject to the terms of the Mozilla Public
// License, v. 2.0. If a copy of the MPL was not distributed with this file,
// You can obtain one at https://mozilla.org/MPL/2.0/.

namespace BrokeYourBike\ZenithBank\Tests;

use Psr\SimpleCache\CacheInterface;
use Psr\Http\Message\ResponseInterface;
use BrokeYourBike\ZenithBank\Interfaces\TransactionInterface;
use BrokeYourBike\ZenithBank\Interfaces\ConfigInterface;
use BrokeYourBike\ZenithBank\Client;

/**
 * @author Ivan Stasiuk <brokeyourbike@gmail.com>
 */
class SendDomesticTransactionTest extends TestCase
{
    private string $authToken = 'secure-token';

    /**
     * @test
     * @dataProvider isLiveProvider
     */
    public function it_can_prepare_request(bool $isLive): void
    {
        $transaction = $this->getMockBuilder(TransactionInterface::class)->getMock();
        $transaction->method('getReference')->willReturn('REF-1234');
        $transaction->method('getSenderName')->willReturn('John Doe');
        $transaction->method('getRecipientName')->willReturn('Jane Doe');
        $transaction->method('getRecipientAccount')->willReturn('556890');
        $transaction->method('getDebitAccount')->willReturn('448000');
        $transaction->method('getAmount')->willReturn(100.01);
        $transaction->method('shouldResend')->willReturn(false);

        /** @var TransactionInterface $transaction */
        $this->assertInstanceOf(TransactionInterface::class, $transaction);

        $mockedConfig = $this->getMockBuilder(ConfigInterface::class)->getMock();
        $mockedConfig->method('isLive')->willReturn($isLive);
        $mockedConfig->method('getUrl')->willReturn('https://api.example/');

        /** @var \Mockery\MockInterface $mockedClient */
        $mockedClient = \Mockery::mock(\GuzzleHttp\Client::class);
        $mockedClient->shouldReceive('request')->withArgs([
            'POST',
            'https://api.example/api/transaction/zenithDomTransfer',
            [
                \GuzzleHttp\RequestOptions::HTTP_ERRORS => false,
                \GuzzleHttp\RequestOptions::HEADERS => [
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer {$this->authToken}",
                ],
                \GuzzleHttp\RequestOptions::JSON => [
                    'transactionReference' => 'REF-1234',
                    'paymentReference' => 'REF-1234',
                    'senderName' => 'John Doe',
                    'beneficiaryName' => 'Jane Doe',
                    'crAccount' => '556890',
                    'drAccount' => '448000',
                    'amount' => 100.01,
                    'resend' => false,
                ],
            ],
        ])->once();

        $mockedCache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $mockedCache->method('has')->willReturn(true);
        $mockedCache->method('get')->willReturn($this->authToken);

        /**
         * @var ConfigInterface $mockedConfig
         * @var \GuzzleHttp\Client $mockedClient
         * @var CacheInterface $mockedCache
         * */
        $api = new Client($mockedConfig, $mockedClient, $mockedCache);

        $requestResult = $api->sendDomesticTransaction($transaction);

        $this->assertInstanceOf(ResponseInterface::class, $requestResult);
    }

    /**
     * @test
     * @dataProvider isLiveProvider
     */
    public function it_will_pass_source_model_as_option(bool $isLive): void
    {
        /** @var SourceTransactionFixture $transaction */
        $transaction = $this->getMockBuilder(SourceTransactionFixture::class)->getMock();

        $mockedConfig = $this->getMockBuilder(ConfigInterface::class)->getMock();
        $mockedConfig->method('isLive')->willReturn($isLive);
        $mockedConfig->method('getUrl')->willReturn('https://api.example/');

        /** @var \Mockery\MockInterface $mockedClient */
        $mockedClient = \Mockery::mock(\GuzzleHttp\Client::class);
        $mockedClient->shouldReceive('request')->withArgs([
            'POST',
            'https://api.example/api/transaction/zenithDomTransfer',
            [
                \GuzzleHttp\RequestOptions::HTTP_ERRORS => false,
                \GuzzleHttp\RequestOptions::HEADERS => [
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer {$this->authToken}",
                ],
                \GuzzleHttp\RequestOptions::JSON => [
                    'transactionReference' => $transaction->getReference(),
                    'paymentReference' => $transaction->getReference(),
                    'senderName' => $transaction->getSenderName(),
                    'beneficiaryName' => $transaction->getRecipientName(),
                    'crAccount' => $transaction->getRecipientAccount(),
                    'drAccount' => $transaction->getDebitAccount(),
                    'amount' => $transaction->getAmount(),
                    'resend' => $transaction->shouldResend(),
                ],
                \BrokeYourBike\HasSourceModel\Enums\RequestOptions::SOURCE_MODEL => $transaction,
            ],
        ])->once();

        $mockedCache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $mockedCache->method('has')->willReturn(true);
        $mockedCache->method('get')->willReturn($this->authToken);

        /**
         * @var ConfigInterface $mockedConfig
         * @var \GuzzleHttp\Client $mockedClient
         * @var CacheInterface $mockedCache
         * */
        $api = new Client($mockedConfig, $mockedClient, $mockedCache);

        $requestResult = $api->sendDomesticTransaction($transaction);

        $this->assertInstanceOf(ResponseInterface::class, $requestResult);
    }
}
