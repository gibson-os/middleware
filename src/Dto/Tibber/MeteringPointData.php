<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Dto\Tibber;

readonly class MeteringPointData
{
    public function __construct(
        private string $consumptionEan,
        private string $gridCompany,
        private string $gridAreaCode,
        private string $priceAreaCode,
        private ?string $productionEan,
        private string $energyTaxType,
        private string $vatType,
        private int $estimatedAnnualConsumption,
    ) {
    }
}
