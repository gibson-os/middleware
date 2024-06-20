<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Builder\Tibber;

class SubscriptionQueryBuilder
{
    private ?LegalEntityQueryBuilder $subscriberQueryBuilder = null;

    private ?PriceInfoQueryBuilder $priceInfoQueryBuilder = null;

    private ?PriceRatingQueryBuilder $priceRatingQueryBuilder = null;

    public function build(): array
    {
        $query = [
            'id',
            'validFrom',
            'validTo',
            'status',
        ];

        if ($this->subscriberQueryBuilder !== null) {
            $query['subscriber'] = $this->subscriberQueryBuilder->build();
        }

        if ($this->priceInfoQueryBuilder !== null) {
            $query['priceInfo'] = $this->priceInfoQueryBuilder->build();
        }

        if ($this->priceRatingQueryBuilder !== null) {
            $query['priceRating'] = $this->priceRatingQueryBuilder->build();
        }

        return $query;
    }

    public function withSubscriberQueryBuilder(LegalEntityQueryBuilder $subscriberQueryBuilder): SubscriptionQueryBuilder
    {
        $this->subscriberQueryBuilder = $subscriberQueryBuilder;

        return $this;
    }

    public function withPriceInfoQueryBuilder(PriceInfoQueryBuilder $priceInfoQueryBuilder): SubscriptionQueryBuilder
    {
        $this->priceInfoQueryBuilder = $priceInfoQueryBuilder;

        return $this;
    }

    public function withPriceRatingQueryBuilder(PriceRatingQueryBuilder $priceRatingQueryBuilder): SubscriptionQueryBuilder
    {
        $this->priceRatingQueryBuilder = $priceRatingQueryBuilder;

        return $this;
    }
}
