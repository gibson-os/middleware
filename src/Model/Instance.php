<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Model;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Key;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\User;

/**
 * @method Instance setUser(User $user)
 * @method User     getUser()
 */
#[Table]
class Instance extends AbstractModel implements \JsonSerializable
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column(length: 255)]
    #[Key(true)]
    private string $url;

    #[Column(length: 256)]
    #[Key(true)]
    private string $token;

    #[Column]
    private \DateTimeImmutable $expireDate;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    #[Key(true)]
    private int $userId;

    #[Constraint]
    protected User $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Instance
    {
        $this->id = $id;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): Instance
    {
        $this->url = $url;

        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): Instance
    {
        $this->token = $token;

        return $this;
    }

    public function getExpireDate(): \DateTimeImmutable
    {
        return $this->expireDate;
    }

    public function setExpireDate(\DateTimeImmutable $expireDate): Instance
    {
        $this->expireDate = $expireDate;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): Instance
    {
        $this->userId = $userId;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'url' => $this->url,
            'token' => $this->token,
        ];
    }
}
