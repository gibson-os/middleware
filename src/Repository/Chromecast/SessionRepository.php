<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Repository\Chromecast;

use DateTimeInterface;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Module\Middleware\Model\Chromecast\Session;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionException;

class SessionRepository extends AbstractRepository
{
    /**
     * @throws JsonException
     * @throws ClientException
     * @throws RecordException
     * @throws ReflectionException
     *
     * @return Session[]
     */
    public function getLastUpdateOlderThan(DateTimeInterface $olderThan): array
    {
        return $this->fetchAll('`last_update`<?', [$olderThan->format('Y-m-d H:i:s')], Session::class);
    }
}
