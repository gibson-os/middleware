<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Builder\Tibber;

class HomeQueryBuilder
{
    private ?AddressQueryBuilder $addressQueryBuilder = null;

    private ?LegalEntityQueryBuilder $ownerQueryBuilder = null;

    private ?MeteringPointDataQueryBuilder $meteringPointDataQueryBuilder = null;

    private ?SubscriptionQueryBuilder $subscriptionsQueryBuilder = null;

    private ?FeaturesQueryBuilder $featuresQueryBuilder = null;

    private ?SubscriptionQueryBuilder $currentSubscriptionQuery = null;

    public function build(): array
    {
        $query = [
            'id',
            'appNickname',
            'appAvatar',
            'size',
            'type',
            'numberOfResidents',
            'primaryHeatingSource',
            'hasVentilationSystem',
            'mainFuseSize',
        ];

        if ($this->addressQueryBuilder instanceof AddressQueryBuilder) {
            $query['address'] = $this->addressQueryBuilder->build();
        }

        if ($this->ownerQueryBuilder instanceof LegalEntityQueryBuilder) {
            $query['owner'] = $this->ownerQueryBuilder->build();
        }

        if ($this->meteringPointDataQueryBuilder instanceof MeteringPointDataQueryBuilder) {
            $query['meteringPointData'] = $this->meteringPointDataQueryBuilder->build();
        }

        if ($this->subscriptionsQueryBuilder instanceof SubscriptionQueryBuilder) {
            $query['subscriptions'] = $this->subscriptionsQueryBuilder->build();
        }

        if ($this->featuresQueryBuilder instanceof FeaturesQueryBuilder) {
            $query['features'] = $this->featuresQueryBuilder->build();
        }

        if ($this->currentSubscriptionQuery instanceof SubscriptionQueryBuilder) {
            $query['currentSubscription'] = $this->currentSubscriptionQuery->build();
        }

        return $query;
    }

    public function withAddressQueryBuilder(AddressQueryBuilder $addressQueryBuilder): HomeQueryBuilder
    {
        $this->addressQueryBuilder = $addressQueryBuilder;

        return $this;
    }

    public function withOwnerQueryBuilder(LegalEntityQueryBuilder $ownerQueryBuilder): HomeQueryBuilder
    {
        $this->ownerQueryBuilder = $ownerQueryBuilder;

        return $this;
    }

    public function withMeteringPointDataQueryBuilder(MeteringPointDataQueryBuilder $meteringPointDataQueryBuilder): HomeQueryBuilder
    {
        $this->meteringPointDataQueryBuilder = $meteringPointDataQueryBuilder;

        return $this;
    }

    public function withSubscriptionQueryBuilder(SubscriptionQueryBuilder $subscriptionQueryBuilder): HomeQueryBuilder
    {
        $this->subscriptionsQueryBuilder = $subscriptionQueryBuilder;

        return $this;
    }

    public function withFeaturesQueryBuilder(FeaturesQueryBuilder $featuresQueryBuilder): HomeQueryBuilder
    {
        $this->featuresQueryBuilder = $featuresQueryBuilder;

        return $this;
    }

    public function withCurrentSubscriptionQuery(SubscriptionQueryBuilder $currentSubscriptionQuery): HomeQueryBuilder
    {
        $this->currentSubscriptionQuery = $currentSubscriptionQuery;

        return $this;
    }
}
