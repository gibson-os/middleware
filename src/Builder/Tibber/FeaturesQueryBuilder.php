<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Builder\Tibber;

class FeaturesQueryBuilder
{
    public function build(): array
    {
        return [
            'realTimeConsumptionEnabled',
        ];
    }
}
