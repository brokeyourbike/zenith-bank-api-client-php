<?php

namespace BrokeYourBike\ZenithBank\Enums;

/**
 * @author Ivan Stasiuk <brokeyourbike@gmail.com>
 *
 * @method static PostedStatusEnum YES()
 * @method static PostedStatusEnum NO()
 * @psalm-immutable
 */
final class PostedStatusEnum extends \MyCLabs\Enum\Enum
{
    private const YES = 'Y';
    private const NO = 'N';
}
