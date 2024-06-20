<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Builder\Tibber;

class ThresholdPercentagesQueryBuilder
{
    public function build(): array
    {
        return [
            'high',
            'low',
        ];
    }
}
