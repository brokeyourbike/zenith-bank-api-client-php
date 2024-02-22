<?php

// Copyright (C) 2021 Ivan Stasiuk <ivan@stasi.uk>.
//
// This Source Code Form is subject to the terms of the Mozilla Public
// License, v. 2.0. If a copy of the MPL was not distributed with this file,
// You can obtain one at https://mozilla.org/MPL/2.0/.

namespace BrokeYourBike\ZenithBank\Models;

use BrokeYourBike\ZenithBank\Enums\StatusCodeEnum;
use BrokeYourBike\ZenithBank\Enums\PostedStatusEnum;
use BrokeYourBike\ZenithBank\Enums\ErrorCodeEnum;
use BrokeYourBike\DataTransferObject\JsonResponse;

/**
 * @author Ivan Stasiuk <ivan@stasi.uk>
 */
class FetchDomesticTransactionResponse extends JsonResponse
{
    public string $responseCode;
    public string $responseDescription;
    public ?string $description;
    public ?string $drAccount;
    public ?string $crAccount;
    public ?string $amount;
    public ?string $senderName;
    public ?string $beneficiaryName;
    public ?string $transactionReference;
    public ?string $paymentReference;
    public ?string $createDate;
    public ?string $transactionStatus;
    public ?string $posted;
    public ?string $postingReference;

    public function paid(): bool
    {
        return $this->responseCode === ErrorCodeEnum::SUCCESS->value 
            && $this->posted === PostedStatusEnum::YES->value
            && $this->transactionStatus === StatusCodeEnum::PROCESSED->value;
    }
}
