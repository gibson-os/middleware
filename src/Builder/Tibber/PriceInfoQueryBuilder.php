<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Builder\Tibber;

class PriceInfoQueryBuilder
{
    private ?PriceQueryBuilder $currentQueryBuilder = null;

    private ?PriceQueryBuilder $today = null;

    private ?PriceQueryBuilder $tomorrow = null;

    public function build(): array
    {
        $query = [];

        if ($this->currentQueryBuilder !== null) {
            $query['current'] = $this->currentQueryBuilder->build();
        }

        if ($this->today !== null) {
            $query['today'] = $this->today->build();
        }

        if ($this->tomorrow !== null) {
            $query['tomorrow'] = $this->tomorrow->build();
        }

        return $query;
    }
}
