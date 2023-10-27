<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Model;

use DateTimeImmutable;
use DateTimeInterface;
use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Enum\Middleware\Message\Priority;
use GibsonOS\Core\Enum\Middleware\Message\Type;
use GibsonOS\Core\Enum\Middleware\Message\Vibrate;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Core\Wrapper\ModelWrapper;
use JsonException;
use JsonSerializable;

/**
 * @method Message  setInstance(Instance $instance)
 * @method Instance getInstance()
 */
#[Table]
class Message extends AbstractModel implements JsonSerializable
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column(length: 512)]
    private ?string $token = null;

    #[Column(length: 512)]
    private string $fcmToken;

    #[Column]
    private Type $type = Type::NOTIFICATION;

    #[Column(length: 512)]
    private ?string $title = null;

    #[Column(length: 512)]
    private ?string $body = null;

    #[Column(length: 32)]
    private string $module;

    #[Column(length: 32)]
    private string $task;

    #[Column(length: 32)]
    private string $action;

    #[Column]
    private array $data = [];

    #[Column]
    private Priority $priority = Priority::NORMAL;

    #[Column]
    private ?Vibrate $vibrate = null;

    #[Column]
    private DateTimeInterface $added;

    #[Column]
    private ?DateTimeInterface $sent = null;

    #[Column]
    private bool $notFound = false;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $instanceId;

    #[Constraint]
    protected Instance $instance;

    public function __construct(ModelWrapper $modelWrapper)
    {
        parent::__construct($modelWrapper);

        $this->added = new DateTimeImmutable();
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

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): Message
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

    public function getAdded(): DateTimeInterface
    {
        return $this->added;
    }

    public function setAdded(DateTimeInterface $added): Message
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

    public function getSent(): ?DateTimeInterface
    {
        return $this->sent;
    }

    public function setSent(?DateTimeInterface $sent): Message
    {
        $this->sent = $sent;

        return $this;
    }

    public function isNotFound(): bool
    {
        return $this->notFound;
    }

    public function setNotFound(bool $notFound): Message
    {
        $this->notFound = $notFound;

        return $this;
    }

    /**
     * @throws JsonException
     */
    public function jsonSerialize(): array
    {
        $data = [
            'token' => $this->getFcmToken(),
            'android' => [
                'priority' => $this->priority->value,
            ],
        ];

        $data['data'] = [
            'token' => $this->getToken(),
            'type' => $this->getType()->value,
            'module' => $this->getModule(),
            'task' => $this->getTask(),
            'action' => $this->getAction(),
            'vibrate' => JsonUtility::encode($this->getVibrate()?->getPattern() ?? []),
            'title' => $this->getTitle(),
            'body' => $this->getBody(),
        ];

        if (count($this->getData())) {
            $data['data']['payload'] = JsonUtility::encode($this->getData(), JSON_THROW_ON_ERROR);
        }

        return $data;
    }
}
