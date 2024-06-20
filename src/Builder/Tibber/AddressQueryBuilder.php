<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Builder\Tibber;

class AddressQueryBuilder
{
    public function build(): array
    {
        return [
            'address1',
            'address2',
            'address3',
            'city',
            'postalCode',
            'country',
            'latitude',
            'longitude',
        ];
    }
}
