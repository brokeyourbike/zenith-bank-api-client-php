<?php

// Copyright (C) 2021 Ivan Stasiuk <ivan@stasi.uk>.
//
// This Source Code Form is subject to the terms of the Mozilla Public
// License, v. 2.0. If a copy of the MPL was not distributed with this file,
// You can obtain one at https://mozilla.org/MPL/2.0/.

namespace BrokeYourBike\ZenithBank\Tests;

use Psr\SimpleCache\CacheInterface;
use Psr\Http\Message\ResponseInterface;
use BrokeYourBike\ZenithBank\Models\FetchDomesticAccountResponse;
use BrokeYourBike\ZenithBank\Interfaces\ConfigInterface;
use BrokeYourBike\ZenithBank\Client;

/**
 * @author Ivan Stasiuk <ivan@stasi.uk>
 */
class FetchDomesticAccountRawTest extends TestCase
{
    private string $authToken = 'secure-token';
    private string $accountNumber = '654987';

    /** @test */
    public function it_can_prepare_request(): void
    {
        $mockedConfig = $this->getMockBuilder(ConfigInterface::class)->getMock();
        $mockedConfig->method('getUrl')->willReturn('https://api.example/');

        $mockedResponse = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $mockedResponse->method('getStatusCode')->willReturn(200);
        $mockedResponse->method('getBody')
            ->willReturn('{
                "responseCode": "00",
                "responseDescription": "SUCCESS",
                "description": null,
                "accountName": "JANE   DOE",
                "accountType": "SA ",
                "branchNo": 127,
                "crncyID": 1
            }');

        /** @var \Mockery\MockInterface $mockedClient */
        $mockedClient = \Mockery::mock(\GuzzleHttp\Client::class);
        $mockedClient->shouldReceive('request')->withArgs([
            'POST',
            'https://api.example/api/enquiry/domAccountEnquiry',
            [

                \GuzzleHttp\RequestOptions::HEADERS => [
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer {$this->authToken}",
                ],
                \GuzzleHttp\RequestOptions::JSON => [
                    'accountNumber' => $this->accountNumber,
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

        $requestResult = $api->fetchDomesticAccountRaw($this->accountNumber);

        $this->assertInstanceOf(FetchDomesticAccountResponse::class, $requestResult);
    }
}
