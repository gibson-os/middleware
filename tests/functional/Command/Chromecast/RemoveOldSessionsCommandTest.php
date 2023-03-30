<?php
declare(strict_types=1);

namespace GibsonOS\Test\Functional\Middleware\Command\Chromecast;

use DateTime;
use DateTimeImmutable;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Module\Middleware\Command\Chromecast\RemoveOldSessionsCommand;
use GibsonOS\Module\Middleware\Model\Chromecast\Session;
use GibsonOS\Module\Middleware\Model\Instance;
use GibsonOS\Test\Functional\Middleware\MiddlewareFunctionalTest;
use mysqlDatabase;
use mysqlTable;
use Prophecy\Prophecy\ObjectProphecy;

class RemoveOldSessionsCommandTest extends MiddlewareFunctionalTest
{
    private RemoveOldSessionsCommand $removeOldSessionsCommand;

    private DateTimeService|ObjectProphecy $dateTimeService;

    protected function _before(): void
    {
        parent::_before();

        $this->dateTimeService = $this->prophesize(DateTimeService::class);
        $this->serviceManager->setService(DateTimeService::class, $this->dateTimeService->reveal());

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
        $minusOneDay = new DateTime('-1 day');
        $modelManager->saveWithoutChildren(
            (new Session())
                ->setId('marvin')
                ->setInstance($instance)
                ->setLastUpdate(new DateTimeImmutable('-1440 minutes'))
        );
        $modelManager->saveWithoutChildren(
            (new Session())
                ->setId('no hope')
                ->setInstance($instance)
                ->setLastUpdate(new DateTimeImmutable('-1441 minutes'))
        );

        $this->dateTimeService->get('-1 day')
            ->shouldBeCalledOnce()
            ->willReturn($minusOneDay)
        ;

        $this->assertEquals(0, $this->removeOldSessionsCommand->execute());

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
