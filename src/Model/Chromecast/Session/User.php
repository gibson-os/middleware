<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Model\Chromecast\Session;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Module\Middleware\Model\Chromecast\Session;

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

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $sessionId;

    #[Column(length: 255)]
    private int $senderId;

    #[Constraint(name: 'fkChromecast_session_userChromecast_session')]
    protected Session $session;

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

    public function getSessionId(): int
    {
        return $this->sessionId;
    }

    public function setSessionId(int $sessionId): User
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    public function getSenderId(): int
    {
        return $this->senderId;
    }

    public function setSenderId(int $senderId): User
    {
        $this->senderId = $senderId;

        return $this;
    }
}
