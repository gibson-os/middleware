<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Builder\Tibber;

class LegalEntityQueryBuilder
{
    private ?ContactInfoQueryBuilder $contactInfoQueryBuilder = null;

    private ?AddressQueryBuilder $addressQueryBuilder = null;

    public function build(): array
    {
        $query = [
            'id',
            'firstName',
            'isCompany',
            'name',
            'middleName',
            'lastName',
            'organizationNo',
            'language',
        ];

        if ($this->contactInfoQueryBuilder instanceof ContactInfoQueryBuilder) {
            $query['contactInfo'] = $this->contactInfoQueryBuilder->build();
        }

        if ($this->addressQueryBuilder instanceof AddressQueryBuilder) {
            $query['address'] = $this->addressQueryBuilder->build();
        }

        return $query;
    }
}
