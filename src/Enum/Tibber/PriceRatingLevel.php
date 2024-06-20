<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Enum\Tibber;

enum PriceRatingLevel: string
{
    case LOW = 'LOW';
    case NORMAL = 'NORMAL';
    case HIGH = 'HIGH';
}
