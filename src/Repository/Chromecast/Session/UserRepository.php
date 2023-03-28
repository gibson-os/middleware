<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Repository\Chromecast\Session;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Module\Middleware\Model\Chromecast\Session;
use GibsonOS\Module\Middleware\Model\Chromecast\Session\User;

class UserRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     */
    public function getFirst(Session $session): User
    {
        return $this->fetchOne('`session_id`=?', [$session->getId()], User::class, '`added`');
    }
}
