<?php

// Copyright (C) 2021 Ivan Stasiuk <ivan@stasi.uk>.
//
// This Source Code Form is subject to the terms of the Mozilla Public
// License, v. 2.0. If a copy of the MPL was not distributed with this file,
// You can obtain one at https://mozilla.org/MPL/2.0/.

namespace BrokeYourBike\ZenithBank\Enums;

/**
 * @author Ivan Stasiuk <ivan@stasi.uk>
 */
enum ErrorCodeEnum: string
{
    case SUCCESS = '00';
    case DUPLICATE_TRANSACTION = '01';
    case UNABLE_TO_CREATE_REQUEST = '02';
    case UNKNOWN_STATE = '03';
    case TRANSACTION_QUERY_DATERANGE_EXPIRED = '04';
    case TRANSACTION_NOT_FOUND = '05';
    case AUTH_TOKEN_REQUIRED = '06';
    case EXPIRED_TOKEN = '07';
    case INVALID_TOKEN = '08';
    case INVALID_USER_CREDENTIALS = '09';
    case INACTIVE_CREDENTIALS = '10';
    case ERROR_PROCESSING_REQUEST = '11';
    case INVALID_CALLER_IP_ADDRESS = '12';
    case WRONG_ACCOUNT_PASSED = '13';
    case SYSTEM_EXCEPTION = '14';
    case INTERNAL_BAD_REQUEST = '15';
}
