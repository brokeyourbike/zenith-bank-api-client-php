<?php

// Copyright (C) 2021 Ivan Stasiuk <brokeyourbike@gmail.com>.
//
// This Source Code Form is subject to the terms of the Mozilla Public
// License, v. 2.0. If a copy of the MPL was not distributed with this file,
// You can obtain one at https://mozilla.org/MPL/2.0/.

namespace BrokeYourBike\ZenithBank\Tests\Enums;

use PHPUnit\Framework\TestCase;
use BrokeYourBike\ZenithBank\Enums\StatusCodeEnum;

/**
 * @author Ivan Stasiuk <brokeyourbike@gmail.com>
 */
class StatusCodeEnumTest extends TestCase
{
    /** @test */
    public function it_extends_myclabs_enum(): void
    {
        $parent = get_parent_class(StatusCodeEnum::class);

        $this->assertSame(\MyCLabs\Enum\Enum::class, $parent);
    }

    /** @test */
    public function it_has_not_duplicate_values()
    {
        $allValuesRaw = StatusCodeEnum::toArray();
        $this->assertNotEmpty($allValuesRaw);

        $uniqueValuesraw = array_unique($allValuesRaw, SORT_STRING);

        $this->assertEquals($allValuesRaw, $uniqueValuesraw);
    }
}
