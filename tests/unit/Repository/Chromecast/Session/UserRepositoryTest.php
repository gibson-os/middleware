<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Middleware\Repository\Chromecast\Session;

use Codeception\Test\Unit;
use DateTimeImmutable;
use GibsonOS\Module\Middleware\Model\Chromecast\Session;
use GibsonOS\Module\Middleware\Model\Chromecast\Session\User;
use GibsonOS\Module\Middleware\Repository\Chromecast\Session\UserRepository;
use GibsonOS\Test\Unit\Core\Repository\RepositoryTrait;
use MDO\Dto\Query\Where;
use MDO\Query\SelectQuery;

class UserRepositoryTest extends Unit
{
    use RepositoryTrait;

    private UserRepository $userRepository;

    protected function _before()
    {
        $this->loadRepository('middleware_chromecast_session_user');

        $this->userRepository = new UserRepository($this->repositoryWrapper->reveal());
    }

    public function testGetFirst(): void
    {
        $selectQuery = (new SelectQuery($this->table, 't'))
            ->addWhere(new Where('`session_id`=?', ['galaxy']))
            ->setOrder('`added`')
            ->setLimit(1)
        ;

        $session = (new Session($this->modelWrapper->reveal()))->setId('galaxy');
        $model = $this->loadModel($selectQuery, User::class);
        $user = $this->userRepository->getFirst($session);
        $date = new DateTimeImmutable();
        $model->setAdded($date);
        $user->setAdded($date);

        $this->assertEquals($model, $user);
    }
}
