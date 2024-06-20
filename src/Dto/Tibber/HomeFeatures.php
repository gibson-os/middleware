<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Dto\Tibber;

readonly class HomeFeatures
{
    public function __construct(private bool $realTimeConsumptionEnabled)
    {
    }

    public function isRealTimeConsumptionEnabled(): bool
    {
        return $this->realTimeConsumptionEnabled;
    }
}
