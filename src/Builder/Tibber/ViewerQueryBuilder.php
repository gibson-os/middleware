<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Builder\Tibber;

class ViewerQueryBuilder
{
    private ?HomeQueryBuilder $homeQueryBuilder = null;

    public function build(): array
    {
        $query = [
            'name',
            'login',
            'userId',
            'accountType',
            'accountType',
        ];

        if ($this->homeQueryBuilder instanceof HomeQueryBuilder) {
            $query['home'] = $this->homeQueryBuilder->build();
        }

        return $query;
    }

    public function withHomeQueryBuilder(HomeQueryBuilder $homeQueryBuilder): ViewerQueryBuilder
    {
        $this->homeQueryBuilder = $homeQueryBuilder;

        return $this;
    }
}
