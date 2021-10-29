<?php

// Copyright (C) 2021 Ivan Stasiuk <brokeyourbike@gmail.com>.
//
// This Source Code Form is subject to the terms of the Mozilla Public
// License, v. 2.0. If a copy of the MPL was not distributed with this file,
// You can obtain one at https://mozilla.org/MPL/2.0/.

namespace BrokeYourBike\ZenithBank\Enums;

/**
 * @author Ivan Stasiuk <brokeyourbike@gmail.com>
 *
 * @method static ErrorCodeEnum SUCCESS()
 * @method static ErrorCodeEnum DUPLICATE_TRANSACTION()
 * @method static ErrorCodeEnum WRONG_REQUEST()
 * @method static ErrorCodeEnum UNAUTHENTICATED()
 * @method static ErrorCodeEnum ERROR()
 * @method static ErrorCodeEnum INVALID_ACCOUNT()
 * @psalm-immutable
 */
final class ErrorCodeEnum extends \MyCLabs\Enum\Enum
{
    private const SUCCESS = '00';
    private const DUPLICATE_TRANSACTION = '01';
    private const WRONG_REQUEST = '02';
    private const UNAUTHENTICATED = '06';
    private const ERROR = '11';
    private const INVALID_ACCOUNT = '13';
    private const SYSTEM_EXCEPTION = '14';
}
