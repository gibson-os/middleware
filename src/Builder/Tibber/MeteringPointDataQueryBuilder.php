<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Builder\Tibber;

class MeteringPointDataQueryBuilder
{
    public function build(): array
    {
        return [
            'consumptionEan',
            'gridCompany',
            'gridAreaCode',
            'priceAreaCode',
            'productionEan',
            'energyTaxType',
            'vatType',
            'estimatedAnnualConsumption',
        ];
    }
}
