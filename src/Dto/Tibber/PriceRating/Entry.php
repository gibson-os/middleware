<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Dto\Tibber\PriceRating;

use DateTimeImmutable;
use GibsonOS\Module\Middleware\Enum\Tibber\PriceRatingLevel;

readonly class Entry
{
    public function __construct(
        private DateTimeImmutable $time,
        private float $energy,
        private float $total,
        private PriceRatingLevel $level,
    ) {
    }
}
