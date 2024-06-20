<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Enum\Tibber;

enum PriceLevel: string
{
    case VERY_CHEAP = 'VERY_CHEAP';
    case CHEAP = 'CHEAP';
    case NORMAL = 'NORMAL';
    case EXPENSIVE = 'EXPENSIVE';
    case VERY_EXPENSIVE = 'VERY_EXPENSIVE';
}
