<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Dto\Tibber;

readonly class Viewer
{
    /**
     * @param string[] $accountType
     * @param Home[]   $homes
     */
    public function __construct(
        private string $name,
        private string $login,
        private string $userId,
        private array $accountType,
        private array $homes = [],
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * @return string[]
     */
    public function getAccountType(): array
    {
        return $this->accountType;
    }

    /**
     * @return Home[]
     */
    public function getHomes(): array
    {
        return $this->homes;
    }
}
