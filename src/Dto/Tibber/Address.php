<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Dto\Tibber;

readonly class Address
{
    public function __construct(
        private string $address1,
        private string $address2,
        private string $address3,
        private string $city,
        private string $postalCode,
        private string $country,
        private string $latitude,
        private string $longitude,
    ) {
    }

    public function getAddress1(): string
    {
        return $this->address1;
    }

    public function getAddress2(): string
    {
        return $this->address2;
    }

    public function getAddress3(): string
    {
        return $this->address3;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getLatitude(): string
    {
        return $this->latitude;
    }

    public function getLongitude(): string
    {
        return $this->longitude;
    }
}
