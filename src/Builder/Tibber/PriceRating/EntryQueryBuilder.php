<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Builder\Tibber\PriceRating;

class EntryQueryBuilder
{
    public function build(): array
    {
        return [
            'time',
            'energy',
            'total',
            'level',
        ];
    }
}
