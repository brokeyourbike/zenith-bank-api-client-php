<?php

// Copyright (C) 2021 Ivan Stasiuk <ivan@stasi.uk>.
//
// This Source Code Form is subject to the terms of the Mozilla Public
// License, v. 2.0. If a copy of the MPL was not distributed with this file,
// You can obtain one at https://mozilla.org/MPL/2.0/.

namespace BrokeYourBike\ZenithBank\Tests;

use Psr\SimpleCache\CacheInterface;
use Psr\Http\Message\ResponseInterface;
use BrokeYourBike\ZenithBank\Models\SendTransactionResponse;
use BrokeYourBike\ZenithBank\Interfaces\TransactionInterface;
use BrokeYourBike\ZenithBank\Interfaces\ConfigInterface;
use BrokeYourBike\ZenithBank\Client;

/**
 * @author Ivan Stasiuk <ivan@stasi.uk>
 */
class SendTransactionTest extends TestCase
{
    private string $authToken = 'secure-token';

    /** @test */
    public function it_can_prepare_request(): void
    {
        $transaction = $this->getMockBuilder(TransactionInterface::class)->getMock();
        $transaction->method('getReference')->willReturn('REF-1234');
        $transaction->method('getRecipientAccount')->willReturn('556890');
        $transaction->method('getDebitAccount')->willReturn('448000');
        $transaction->method('getAmount')->willReturn(100.01);

        /** @var TransactionInterface $transaction */
        $this->assertInstanceOf(TransactionInterface::class, $transaction);

        $mockedConfig = $this->getMockBuilder(ConfigInterface::class)->getMock();
        $mockedConfig->method('getUrl')->willReturn('https://api.example/');

        $mockedResponse = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $mockedResponse->method('getStatusCode')->willReturn(200);
        $mockedResponse->method('getBody')
            ->willReturn('{
                "responseCode": "01",
                "responseDescription": "DUPLICATE TRANSACTION REFERENCE",
                "description": "Suspected Duplicate with transaction Reference 405",
                "transactionReference": "405",
                "posted": "N",
                "transactionStatus": null,
                "postingDate": null,
                "postingReference": null
            }');

        /** @var \Mockery\MockInterface $mockedClient */
        $mockedClient = \Mockery::mock(\GuzzleHttp\Client::class);
        $mockedClient->shouldReceive('request')->withArgs([
            'POST',
            'https://api.example/api/transaction/zenithTransfer',
            [

                \GuzzleHttp\RequestOptions::HEADERS => [
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer {$this->authToken}",
                ],
                \GuzzleHttp\RequestOptions::JSON => [
                    'amount' => 100.01,
                    'bankName' => 'zenith',
                    'crAccount' => '556890',
                    'drAccount' => '448000',
                    'transactionReference' => 'REF-1234',
                    'description' => 'REF-1234',
                ],
            ],
        ])->once()->andReturn($mockedResponse);

        $mockedCache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $mockedCache->method('has')->willReturn(true);
        $mockedCache->method('get')->willReturn($this->authToken);

        /**
         * @var ConfigInterface $mockedConfig
         * @var \GuzzleHttp\Client $mockedClient
         * @var CacheInterface $mockedCache
         * */
        $api = new Client($mockedConfig, $mockedClient, $mockedCache);

        $requestResult = $api->sendTransaction($transaction);

        $this->assertInstanceOf(SendTransactionResponse::class, $requestResult);
        $this->assertFalse($requestResult->paid());
    }
}
