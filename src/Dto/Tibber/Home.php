<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Dto\Tibber;

use GibsonOS\Module\Middleware\Enum\Tibber\Avatar;
use GibsonOS\Module\Middleware\Enum\Tibber\HeatingSource;
use GibsonOS\Module\Middleware\Enum\Tibber\Type;

readonly class Home
{
    /**
     * @param Subscription[] $subscriptions
     */
    public function __construct(
        private string $id,
        private string $appNickname,
        private ?Avatar $appAvatar,
        private ?Type $type,
        private ?string $timeZone,
        private int $size,
        private int $numberOfResidents,
        private ?HeatingSource $primaryHeatingSource,
        private bool $hasVentilationSystem,
        private int $mainFuseSize,
        private ?LegalEntity $owner,
        private ?MeteringPointData $meteringPointData,
        private array $subscriptions,
        private ?HomeFeatures $features,
        private ?Subscription $currentSubscription,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getAppNickname(): string
    {
        return $this->appNickname;
    }

    public function getAppAvatar(): ?Avatar
    {
        return $this->appAvatar;
    }

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function getTimeZone(): ?string
    {
        return $this->timeZone;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getNumberOfResidents(): int
    {
        return $this->numberOfResidents;
    }

    public function getPrimaryHeatingSource(): ?HeatingSource
    {
        return $this->primaryHeatingSource;
    }

    public function isHasVentilationSystem(): bool
    {
        return $this->hasVentilationSystem;
    }

    public function getMainFuseSize(): int
    {
        return $this->mainFuseSize;
    }

    public function getOwner(): ?LegalEntity
    {
        return $this->owner;
    }

    public function getMeteringPointData(): ?MeteringPointData
    {
        return $this->meteringPointData;
    }

    public function getSubscriptions(): array
    {
        return $this->subscriptions;
    }

    public function getFeatures(): ?HomeFeatures
    {
        return $this->features;
    }

    public function getCurrentSubscription(): ?Subscription
    {
        return $this->currentSubscription;
    }
}
