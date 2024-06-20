<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Dto\Tibber\PriceRating;

readonly class ThresholdPercentages
{
    public function __construct(
        private ?float $low,
        private ?float $high,
    ) {
    }

    public function getLow(): ?float
    {
        return $this->low;
    }

    public function getHigh(): ?float
    {
        return $this->high;
    }
}
