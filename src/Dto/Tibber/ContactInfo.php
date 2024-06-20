<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Dto\Tibber;

readonly class ContactInfo
{
    public function __construct(
        private string $email,
        private string $mobile,
        private ?Address $address,
    ) {
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getMobile(): string
    {
        return $this->mobile;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }
}
