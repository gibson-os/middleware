<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Model\Chromecast\Session;

use DateTimeImmutable;
use DateTimeInterface;
use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Module\Middleware\Model\Chromecast\Session;
use mysqlDatabase;

/**
 * @method Session getSession()
 * @method User    setSession(Session $session)
 */
#[Table]
class User extends AbstractModel
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $userId;

    #[Column(length: 48)]
    private string $sessionId;

    #[Column(length: 128)]
    private string $senderId;

    #[Column]
    private DateTimeInterface $added;

    #[Constraint(name: 'fkChromecast_session_userChromecast_session')]
    protected Session $session;

    public function __construct(mysqlDatabase $database = null)
    {
        parent::__construct($database);

        $this->added = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): User
    {
        $this->id = $id;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): User
    {
        $this->userId = $userId;

        return $this;
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function setSessionId(string $sessionId): User
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    public function getSenderId(): string
    {
        return $this->senderId;
    }

    public function setSenderId(string $senderId): User
    {
        $this->senderId = $senderId;

        return $this;
    }

    public function getAdded(): DateTimeInterface
    {
        return $this->added;
    }

    public function setAdded(DateTimeInterface $added): User
    {
        $this->added = $added;

        return $this;
    }
}
