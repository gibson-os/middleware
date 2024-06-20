<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Dto\Tibber;

use DateTimeImmutable;

readonly class Subscription
{
    public function __construct(
        private string $id,
        private ?LegalEntity $subscriber,
        private ?DateTimeImmutable $validFrom,
        private ?DateTimeImmutable $validTo,
        private string $status,
        private ?PriceInfo $priceInfo,
        private ?PriceRating $priceRating,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSubscriber(): ?LegalEntity
    {
        return $this->subscriber;
    }

    public function getValidFrom(): ?DateTimeImmutable
    {
        return $this->validFrom;
    }

    public function getValidTo(): ?DateTimeImmutable
    {
        return $this->validTo;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getPriceInfo(): ?PriceInfo
    {
        return $this->priceInfo;
    }

    public function getPriceRating(): ?PriceRating
    {
        return $this->priceRating;
    }
}
