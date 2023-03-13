<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Model\Chromecast;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;

/**
 * @method Error   setSession(Session $session)
 * @method Session getSession()
 */
#[Table]
class Error extends AbstractModel
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column(length: 1024)]
    private string $message;

    #[Column]
    private \DateTimeInterface $added;

    #[Column(length: 48)]
    private string $sessionId;

    #[Constraint]
    protected Session $session;

    public function __construct(\mysqlDatabase $database = null)
    {
        parent::__construct($database);

        $this->added = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Error
    {
        $this->id = $id;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): Error
    {
        $this->message = $message;

        return $this;
    }

    public function getAdded(): \DateTimeInterface
    {
        return $this->added;
    }

    public function setAdded(\DateTimeInterface $added): Error
    {
        $this->added = $added;

        return $this;
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function setSessionId(string $sessionId): Error
    {
        $this->sessionId = $sessionId;

        return $this;
    }
}
