<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Dto\Tibber;

readonly class PriceInfo
{
    /**
     * @param Price[] $today
     * @param Price[] $tomorrow
     */
    public function __construct(
        private ?Price $current,
        private array $today,
        private array $tomorrow,
    ) {
    }

    public function getCurrent(): ?Price
    {
        return $this->current;
    }

    /**
     * @return Price[]
     */
    public function getToday(): array
    {
        return $this->today;
    }

    /**
     * @return Price[]
     */
    public function getTomorrow(): array
    {
        return $this->tomorrow;
    }
}
