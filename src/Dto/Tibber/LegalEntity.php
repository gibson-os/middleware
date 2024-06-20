<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Dto\Tibber;

readonly class LegalEntity
{
    public function __construct(
        private string $id,
        private string $firstName,
        private ?string $middleName,
        private string $lastName,
        private string $name,
        private ?bool $isCompany,
        private ?string $organizationNo,
        private string $language,
        private ?ContactInfo $contactInfo,
        private ?Address $address,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getIsCompany(): ?bool
    {
        return $this->isCompany;
    }

    public function getOrganizationNo(): ?string
    {
        return $this->organizationNo;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getContactInfo(): ?ContactInfo
    {
        return $this->contactInfo;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }
}
