<?php

// Copyright (C) 2021 Ivan Stasiuk <brokeyourbike@gmail.com>.
//
// This Source Code Form is subject to the terms of the Mozilla Public
// License, v. 2.0. If a copy of the MPL was not distributed with this file,
// You can obtain one at https://mozilla.org/MPL/2.0/.

namespace BrokeYourBike\ZenithBank\Tests;

use PHPUnit\Framework\TestCase;
use BrokeYourBike\ZenithBank\Interfaces\ConfigInterface;
use BrokeYourBike\ZenithBank\Client;
use BrokeYourBike\ResolveUri\ResolveUriTrait;
use BrokeYourBike\HttpClient\HttpClientTrait;
use BrokeYourBike\HttpClient\HttpClientInterface;
use BrokeYourBike\HasSourceModel\HasSourceModelTrait;
use BrokeYourBike\HasSourceModel\HasSourceModelInterface;

/**
 * @author Ivan Stasiuk <brokeyourbike@gmail.com>
 */
class ClientTest extends TestCase
{
    /** @test */
    public function it_implements_http_client_interface(): void
    {
        /** @var ConfigInterface */
        $mockedConfig = $this->getMockBuilder(ConfigInterface::class)->getMock();

        /** @var \GuzzleHttp\ClientInterface */
        $mockedHttpClient = $this->getMockBuilder(\GuzzleHttp\ClientInterface::class)->getMock();

        /** @var \Psr\SimpleCache\CacheInterface */
        $mockedCache = $this->getMockBuilder(\Psr\SimpleCache\CacheInterface::class)->getMock();

        $api = new Client($mockedConfig, $mockedHttpClient, $mockedCache);

        $this->assertInstanceOf(HttpClientInterface::class, $api);
    }

    /** @test */
    public function it_implements_has_source_model_interface(): void
    {
        /** @var ConfigInterface */
        $mockedConfig = $this->getMockBuilder(ConfigInterface::class)->getMock();

        /** @var \GuzzleHttp\ClientInterface */
        $mockedHttpClient = $this->getMockBuilder(\GuzzleHttp\ClientInterface::class)->getMock();

        /** @var \Psr\SimpleCache\CacheInterface */
        $mockedCache = $this->getMockBuilder(\Psr\SimpleCache\CacheInterface::class)->getMock();

        $api = new Client($mockedConfig, $mockedHttpClient, $mockedCache);

        $this->assertInstanceOf(HasSourceModelInterface::class, $api);
    }

    /** @test */
    public function it_will_return_different_cache_key_for_test_and_sandbox()
    {
        /** @var \GuzzleHttp\ClientInterface */
        $mockedHttpClient = $this->getMockBuilder(\GuzzleHttp\ClientInterface::class)->getMock();

        /** @var \Psr\SimpleCache\CacheInterface */
        $mockedCache = $this->getMockBuilder(\Psr\SimpleCache\CacheInterface::class)->getMock();

        $liveConfig = $this->getMockBuilder(ConfigInterface::class)->getMock();
        $liveConfig->method('isLive')->willReturn(true);

        /** @var ConfigInterface $liveConfig */
        $liveApi = new Client($liveConfig, $mockedHttpClient, $mockedCache);

        $sandboxConfig = $this->getMockBuilder(ConfigInterface::class)->getMock();
        $sandboxConfig->method('isLive')->willReturn(false);

        /** @var ConfigInterface $sandboxConfig */
        $sandboxApi = new Client($sandboxConfig, $mockedHttpClient, $mockedCache);

        $this->assertNotEquals($liveApi->authTokenCacheKey(), $sandboxApi->authTokenCacheKey());
    }

    /** @test */
    public function it_uses_http_client_trait(): void
    {
        $usedTraits = class_uses(Client::class);

        $this->assertArrayHasKey(HttpClientTrait::class, $usedTraits);
    }

    /** @test */
    public function it_uses_resolve_uri_trait(): void
    {
        $usedTraits = class_uses(Client::class);

        $this->assertArrayHasKey(ResolveUriTrait::class, $usedTraits);
    }

    /** @test */
    public function it_uses_has_source_model_trait(): void
    {
        $usedTraits = class_uses(Client::class);

        $this->assertArrayHasKey(HasSourceModelTrait::class, $usedTraits);
    }
}
