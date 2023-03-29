<?php
declare(strict_types=1);

namespace GibsonOS\Test\Functional\Middleware\Command\Chromecast;

use DateTimeImmutable;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Module\Middleware\Command\Chromecast\RemoveOldSessionsCommand;
use GibsonOS\Module\Middleware\Model\Chromecast\Session;
use GibsonOS\Module\Middleware\Model\Instance;
use GibsonOS\Test\Functional\Middleware\MiddlewareFunctionalTest;
use mysqlDatabase;
use mysqlTable;

class RemoveOldSessionsCommandTest extends MiddlewareFunctionalTest
{
    private RemoveOldSessionsCommand $removeOldSessionsCommand;

    protected function _before(): void
    {
        parent::_before();

        $this->removeOldSessionsCommand = $this->serviceManager->get(RemoveOldSessionsCommand::class);
    }

    public function testExecute(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $instance = (new Instance())
            ->setUser($this->addUser())
            ->setUrl('http://arthur.dent/')
            ->setToken('ford')
            ->setSecret('prefect')
            ->setExpireDate(new DateTimeImmutable('+1 hour'))
        ;
        $modelManager->saveWithoutChildren($instance);
        $modelManager->saveWithoutChildren(
            (new Session())
                ->setId('marvin')
                ->setInstance($instance)
                ->setLastUpdate(new DateTimeImmutable('-1439 minutes'))
        );
        $modelManager->saveWithoutChildren(
            (new Session())
                ->setId('no hope')
                ->setInstance($instance)
                ->setLastUpdate(new DateTimeImmutable('-1441 minutes'))
        );

        $this->removeOldSessionsCommand->execute();

        $sessionTable = new mysqlTable(
            $this->serviceManager->get(mysqlDatabase::class),
            'middleware_chromecast_session'
        );

        $this->assertEquals(
            1,
            $sessionTable
                ->setWhere('`id`=?')
                ->setWhereParameters(['marvin'])
                ->selectPrepared(),
        );
        $this->assertEquals(
            0,
            $sessionTable
                ->setWhere('`id`=?')
                ->setWhereParameters(['no hope'])
                ->selectPrepared(),
        );
    }
}
