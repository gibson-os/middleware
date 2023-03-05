<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Model;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Enum\Middleware\Message\Priority;
use GibsonOS\Core\Enum\Middleware\Message\Type;
use GibsonOS\Core\Enum\Middleware\Message\Vibrate;
use GibsonOS\Core\Model\AbstractModel;

/**
 * @method Message  setInstance(Instance $instance)
 * @method Instance getInstance()
 */
#[Table]
class Message extends AbstractModel
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column]
    private string $token;

    #[Column]
    private string $fcmToken;

    #[Column]
    private Type $type = Type::NOTIFICATION;

    private ?string $title = null;

    private ?string $body = null;

    private string $module = 'core';

    private string $task = 'desktop';

    private string $action = 'index';

    private array $data = [];

    private Priority $priority = Priority::NORMAL;

    private ?Vibrate $vibrate = null;

    #[Column]
    private \DateTimeInterface $added;

    #[Column]
    private int $instanceId;

    #[Constraint]
    protected Instance $instance;

    public function __construct(\mysqlDatabase $database = null)
    {
        parent::__construct($database);

        $this->added = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Message
    {
        $this->id = $id;

        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): Message
    {
        $this->token = $token;

        return $this;
    }

    public function getFcmToken(): string
    {
        return $this->fcmToken;
    }

    public function setFcmToken(string $fcmToken): Message
    {
        $this->fcmToken = $fcmToken;

        return $this;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function setType(Type $type): Message
    {
        $this->type = $type;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): Message
    {
        $this->title = $title;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): Message
    {
        $this->body = $body;

        return $this;
    }

    public function getModule(): string
    {
        return $this->module;
    }

    public function setModule(string $module): Message
    {
        $this->module = $module;

        return $this;
    }

    public function getTask(): string
    {
        return $this->task;
    }

    public function setTask(string $task): Message
    {
        $this->task = $task;

        return $this;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): Message
    {
        $this->action = $action;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): Message
    {
        $this->data = $data;

        return $this;
    }

    public function getPriority(): Priority
    {
        return $this->priority;
    }

    public function setPriority(Priority $priority): Message
    {
        $this->priority = $priority;

        return $this;
    }

    public function getVibrate(): ?Vibrate
    {
        return $this->vibrate;
    }

    public function setVibrate(?Vibrate $vibrate): Message
    {
        $this->vibrate = $vibrate;

        return $this;
    }

    public function getAdded(): \DateTimeInterface
    {
        return $this->added;
    }

    public function setAdded(\DateTimeInterface $added): Message
    {
        $this->added = $added;

        return $this;
    }

    public function getInstanceId(): int
    {
        return $this->instanceId;
    }

    public function setInstanceId(int $instanceId): Message
    {
        $this->instanceId = $instanceId;

        return $this;
    }
}
