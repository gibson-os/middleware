<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Middleware\Repository\Chromecast;

use Codeception\Test\Unit;
use DateTimeImmutable;
use GibsonOS\Module\Middleware\Model\Chromecast\Session;
use GibsonOS\Module\Middleware\Repository\Chromecast\SessionRepository;
use GibsonOS\Test\Unit\Core\Repository\RepositoryTrait;
use MDO\Dto\Query\Where;
use MDO\Query\SelectQuery;

class SessionRepositoryTest extends Unit
{
    use RepositoryTrait;

    private SessionRepository $sessionRepository;

    protected function _before()
    {
        $this->loadRepository('middleware_chromecast_session');

        $this->sessionRepository = new SessionRepository($this->repositoryWrapper->reveal());
    }

    public function testGetLastUpdateOlderThan(): void
    {
        $date = new DateTimeImmutable();
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where('`last_update`<?', [$date->format('Y-m-d H:i:s')]))
        ;

        $model = $this->loadModel($selectQuery, Session::class, '');
        $session = $this->sessionRepository->getLastUpdateOlderThan($date)[0];
        $date = new DateTimeImmutable();
        $model
            ->setStarted($date)
            ->setLastUpdate($date)
        ;
        $session
            ->setStarted($date)
            ->setLastUpdate($date)
        ;

        $this->assertEquals($model, $session);
    }
}
