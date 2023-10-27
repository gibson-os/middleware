<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Repository\Chromecast\Session;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Module\Middleware\Model\Chromecast\Session;
use GibsonOS\Module\Middleware\Model\Chromecast\Session\User;
use JsonException;
use MDO\Enum\OrderDirection;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionException;

class UserRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     * @throws JsonException
     * @throws ClientException
     * @throws RecordException
     * @throws ReflectionException
     */
    public function getFirst(Session $session): User
    {
        return $this->fetchOne(
            '`session_id`=?',
            [$session->getId()],
            User::class,
            ['`added`' => OrderDirection::ASC],
        );
    }
}
