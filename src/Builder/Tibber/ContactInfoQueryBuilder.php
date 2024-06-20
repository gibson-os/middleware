<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Builder\Tibber;

class ContactInfoQueryBuilder
{
    public function build(): array
    {
        return [
            'email',
            'mobile',
        ];
    }
}
