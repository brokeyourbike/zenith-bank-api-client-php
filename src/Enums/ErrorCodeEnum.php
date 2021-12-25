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
    case WRONG_REQUEST = '02';
    case UNAUTHENTICATED = '06';
    case ERROR = '11';
    case INVALID_ACCOUNT = '13';
    case SYSTEM_EXCEPTION = '14';
}
