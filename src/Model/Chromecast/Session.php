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
    #[Column(length: 48, primary: true)]
    #[Key(true)]
    private string $id;

    #[Column]
    private \DateTimeInterface $started;

    #[Column]
    private \DateTimeInterface $lastUpdate;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $instanceId;

    #[Constraint]
    protected Instance $instance;

    #[Constraint('session', User::class)]
    protected array $users;

    public function __construct(\mysqlDatabase $database = null)
    {
        parent::__construct($database);

        $this->started = new \DateTimeImmutable();
        $this->lastUpdate = new \DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): Session
    {
        $this->id = $id;

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

    public function getStarted(): \DateTimeInterface
    {
        return $this->started;
    }

    public function setStarted(\DateTimeInterface $started): Session
    {
        $this->started = $started;

        return $this;
    }

    public function getLastUpdate(): \DateTimeInterface
    {
        return $this->lastUpdate;
    }

    public function setLastUpdate(\DateTimeInterface $lastUpdate): Session
    {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }
}
