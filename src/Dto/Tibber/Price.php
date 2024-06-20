<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Dto\Tibber;

use DateTimeImmutable;
use GibsonOS\Module\Middleware\Enum\Tibber\PriceLevel;

readonly class Price
{
    public function __construct(
        private float $total,
        private float $energy,
        private float $tax,
        private DateTimeImmutable $startsAt,
        private ?string $currency,
        private PriceLevel $level,
    ) {
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    public function getEnergy(): float
    {
        return $this->energy;
    }

    public function getTax(): float
    {
        return $this->tax;
    }

    public function getStartsAt(): DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function getLevel(): PriceLevel
    {
        return $this->level;
    }
}
