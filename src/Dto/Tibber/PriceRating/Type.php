<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Dto\Tibber\PriceRating;

readonly class Type
{
    /**
     * @param Entry[] $entries
     */
    public function __construct(
        private ?float $minEnergy,
        private ?float $maxEnergy,
        private ?float $minTotal,
        private ?float $maxTotal,
        private ?string $currency,
        private array $entries,
    ) {
    }

    public function getMinEnergy(): ?float
    {
        return $this->minEnergy;
    }

    public function getMaxEnergy(): ?float
    {
        return $this->maxEnergy;
    }

    public function getMinTotal(): ?float
    {
        return $this->minTotal;
    }

    public function getMaxTotal(): ?float
    {
        return $this->maxTotal;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     * @return Entry[]
     */
    public function getEntries(): array
    {
        return $this->entries;
    }
}
