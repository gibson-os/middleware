<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Model\Chromecast;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Key;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Module\Middleware\Model\Chromecast\Session\User;
use GibsonOS\Module\Middleware\Model\Instance;

/**
 * @method Instance getInstance()
 * @method Session  setInstance(Instance $instance)
 * @method User[]   getUsers()
 * @method Session  setUsers(User[] $users)
 * @method Session  addUsers(User[] $users)
 */
#[Table]
class Session extends AbstractModel
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column(length: 48)]
    #[Key(true)]
    private string $sessionId;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $instanceId;

    #[Constraint]
    protected Instance $instance;

    #[Constraint('session', User::class)]
    protected array $users;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Session
    {
        $this->id = $id;

        return $this;
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function setSessionId(string $sessionId): Session
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    public function getInstanceId(): int
    {
        return $this->instanceId;
    }

    public function setInstanceId(int $instanceId): Session
    {
        $this->instanceId = $instanceId;

        return $this;
    }
}
