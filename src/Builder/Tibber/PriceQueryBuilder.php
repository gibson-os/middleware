<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Builder\Tibber;

class PriceQueryBuilder
{
    public function build(): array
    {
        return [
            'total',
            'energy',
            'tax',
            'startsAt',
            'currency',
            'level',
        ];
    }
}
