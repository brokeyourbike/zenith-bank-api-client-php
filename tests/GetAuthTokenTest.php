<?php

// Copyright (C) 2021 Ivan Stasiuk <brokeyourbike@gmail.com>.
//
// This Source Code Form is subject to the terms of the Mozilla Public
// License, v. 2.0. If a copy of the MPL was not distributed with this file,
// You can obtain one at https://mozilla.org/MPL/2.0/.

namespace BrokeYourBike\ZenithBank\Tests;

use Psr\SimpleCache\CacheInterface;
use Psr\Http\Message\ResponseInterface;
use Carbon\Carbon;
use BrokeYourBike\ZenithBank\Interfaces\ConfigInterface;
use BrokeYourBike\ZenithBank\Client;

/**
 * @author Ivan Stasiuk <brokeyourbike@gmail.com>
 */
class GetAuthTokenTest extends TestCase
{
    private string $tokenValue = 'super-secure-token';

    /**
     * @test
     * @dataProvider isLiveProvider
     */
    public function it_can_cache_and_return_auth_token(bool $isLive)
    {
        $currentTestDate = Carbon::create(2020, 1, 1, 23, 30, 59);
        Carbon::setTestNow($currentTestDate);

        $expirationDate = Carbon::now()->addHour();

        $mockedConfig = $this->getMockBuilder(ConfigInterface::class)->getMock();

        $mockedResponse = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $mockedResponse->method('getStatusCode')->willReturn(200);
        $mockedResponse->method('getBody')
            ->willReturn('{
                "responseCode": "00",
                "responseDescription": "SUCCESS",
                "description": null,
                "tokenDetail": {
                    "token": "' . $this->tokenValue . '",
                    "expiration": "' . (string) $expirationDate . '"
                }
            }');

        /** @var \GuzzleHttp\Client $mockedClient */
        $mockedClient = $this->createPartialMock(\GuzzleHttp\Client::class, ['request']);
        $mockedClient->method('request')->willReturn($mockedResponse);

        /** @var \Mockery\MockInterface $mockedCache */
        $mockedCache = \Mockery::spy(CacheInterface::class);

        /**
         * @var ConfigInterface $mockedConfig
         * @var \GuzzleHttp\Client $mockedClient
         * @var CacheInterface $mockedCache
         * */
        $api = new Client($mockedConfig, $mockedClient, $mockedCache);
        $this->assertSame($this->tokenValue, $api->getAuthToken());

        /** @var \Mockery\MockInterface $mockedCache */
        $mockedCache->shouldHaveReceived('set')
            ->once()
            ->with(
                $api->authTokenCacheKey(),
                $this->tokenValue,
                3600 - 60
            );
    }

    /** @test */
    public function it_will_throw_if_expiration_date_invalid()
    {
        $mockedConfig = $this->getMockBuilder(ConfigInterface::class)->getMock();

        $mockedResponse = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $mockedResponse->method('getStatusCode')->willReturn(200);
        $mockedResponse->method('getBody')
            ->willReturn('{
                "responseCode": "00",
                "responseDescription": "SUCCESS",
                "description": null,
                "tokenDetail": {
                    "token": "' . $this->tokenValue . '",
                    "expiration": "NOT-A-VALID-DATE"
                }
            }');

        /** @var \GuzzleHttp\Client $mockedClient */
        $mockedClient = $this->createPartialMock(\GuzzleHttp\Client::class, ['request']);
        $mockedClient->method('request')->willReturn($mockedResponse);

        /** @var \Mockery\MockInterface $mockedCache */
        $mockedCache = \Mockery::spy(CacheInterface::class);

        /**
         * @var ConfigInterface $mockedConfig
         * @var \GuzzleHttp\Client $mockedClient
         * @var CacheInterface $mockedCache
         * */
        $api = new Client($mockedConfig, $mockedClient, $mockedCache);


        try {
            $api->getAuthToken();
        } catch (\Throwable $th) {
            $this->assertInstanceOf(\Carbon\Exceptions\InvalidFormatException::class, $th);

            /** @var \Mockery\MockInterface $mockedCache */
            $mockedCache->shouldNotHaveReceived('set');

            return;
        }

        $this->fail('Exception was not thrown');
    }

    /** @test */
    public function it_can_return_cached_value()
    {
        $mockedConfig = $this->getMockBuilder(ConfigInterface::class)->getMock();

        /** @var \Mockery\MockInterface $mockedClient */
        $mockedClient = \Mockery::mock(\GuzzleHttp\Client::class);
        $mockedClient->shouldNotReceive('request');

        /** @var \Mockery\MockInterface $mockedCache */
        $mockedCache = \Mockery::mock(CacheInterface::class);
        $mockedCache->shouldReceive('has')
            ->once()
            ->andReturn(true);
        $mockedCache->shouldReceive('get')
            ->once()
            ->andReturn($this->tokenValue);

        /**
         * @var ConfigInterface $mockedConfig
         * @var \GuzzleHttp\Client $mockedClient
         * @var CacheInterface $mockedCache
         * */
        $api = new Client($mockedConfig, $mockedClient, $mockedCache);
        $requestResult = $api->getAuthToken();

        $this->assertSame($this->tokenValue, $requestResult);
    }

    /**
     * @test
     * @dataProvider isLiveProvider
     */
    public function it_will_return_null_if_response_invalid()
    {
        $mockedConfig = $this->getMockBuilder(ConfigInterface::class)->getMock();

        $mockedResponse = $this->getMockBuilder(\Psr\Http\Message\ResponseInterface::class)->getMock();
        $mockedResponse->method('getStatusCode')->willReturn(200);
        $mockedResponse->method('getBody')->willReturn('{}');

        /** @var \GuzzleHttp\Client $mockedClient */
        $mockedClient = $this->createPartialMock(\GuzzleHttp\Client::class, ['request']);
        $mockedClient->method('request')->willReturn($mockedResponse);

        /** @var \Mockery\MockInterface $mockedCache */
        $mockedCache = \Mockery::spy(CacheInterface::class);

        /**
         * @var ConfigInterface $mockedConfig
         * @var \GuzzleHttp\Client $mockedClient
         * @var CacheInterface $mockedCache
         * */
        $api = new Client($mockedConfig, $mockedClient, $mockedCache);
        $requestResult = $api->getAuthToken();

        $this->assertNull($requestResult);

        /** @var \Mockery\MockInterface $mockedCache */
        $mockedCache->shouldNotReceive('set');
    }
}
