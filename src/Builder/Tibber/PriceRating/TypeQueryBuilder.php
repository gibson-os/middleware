<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Builder\Tibber\PriceRating;

class TypeQueryBuilder
{
    private ?EntryQueryBuilder $entriesQueryBuilder = null;

    public function build(): array
    {
        $query = [
            'minTotal',
            'minEnergy',
            'maxTotal',
            'maxEnergy',
            'currency',
        ];

        if ($this->entriesQueryBuilder !== null) {
            $query['entries'] = $this->entriesQueryBuilder->build();
        }

        return $query;
    }

    public function withEntriesQueryBuilder(EntryQueryBuilder $entriesQueryBuilder): TypeQueryBuilder
    {
        $this->entriesQueryBuilder = $entriesQueryBuilder;

        return $this;
    }
}
