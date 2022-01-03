<?php

// Copyright (C) 2021 Ivan Stasiuk <ivan@stasi.uk>.
//
// This Source Code Form is subject to the terms of the Mozilla Public
// License, v. 2.0. If a copy of the MPL was not distributed with this file,
// You can obtain one at https://mozilla.org/MPL/2.0/.

namespace BrokeYourBike\ZenithBank\Models;

use Spatie\DataTransferObject\Attributes\MapFrom;
use BrokeYourBike\DataTransferObject\JsonResponse;

/**
 * @author Ivan Stasiuk <ivan@stasi.uk>
 */
class FetchAuthTokenResponse extends JsonResponse
{
    public string $responseCode;
    public string $responseDescription;
    public ?string $description;

    #[MapFrom('tokenDetail.token')]
    public string $token;

    #[MapFrom('tokenDetail.expiration')]
    public string $expiration;
}
