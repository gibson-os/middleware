<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Dto\Tibber;

use GibsonOS\Module\Middleware\Dto\Tibber\PriceRating\ThresholdPercentages;
use GibsonOS\Module\Middleware\Dto\Tibber\PriceRating\Type;

readonly class PriceRating
{
    public function __construct(
        private ?ThresholdPercentages $thresholdPercentages,
        private ?Type $hourly,
        private ?Type $daily,
        private ?Type $monthly,
    ) {
    }

    public function getThresholdPercentages(): ?ThresholdPercentages
    {
        return $this->thresholdPercentages;
    }

    public function getHourly(): ?Type
    {
        return $this->hourly;
    }

    public function getDaily(): ?Type
    {
        return $this->daily;
    }

    public function getMonthly(): ?Type
    {
        return $this->monthly;
    }
}
