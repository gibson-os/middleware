<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Repository;

use DateTimeInterface;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Module\Middleware\Model\Chromecast\Session;

class SessionRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     *
     * @return Session[]
     */
    public function getLastUpdateOlderThan(DateTimeInterface $olderThan): array
    {
        return $this->fetchAll('`last_update`<?', [$olderThan->format('Y-m-d H:i:s')], Session::class);
    }
}
