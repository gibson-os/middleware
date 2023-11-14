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
use MDO\Client;
use MDO\Dto\Query\Where;
use MDO\Manager\TableManager;
use MDO\Query\SelectQuery;
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
        $instance = (new Instance($this->modelWrapper))
            ->setUser($this->addUser())
            ->setUrl('http://arthur.dent/')
            ->setToken('ford')
            ->setSecret('prefect')
            ->setExpireDate(new DateTimeImmutable('+1 hour'))
        ;
        $modelManager->saveWithoutChildren($instance);
        $minusOneDay = new DateTime('-1 day');
        $modelManager->saveWithoutChildren(
            (new Session($this->modelWrapper))
                ->setId('marvin')
                ->setInstance($instance)
                ->setLastUpdate(new DateTimeImmutable('-1440 minutes'))
        );
        $modelManager->saveWithoutChildren(
            (new Session($this->modelWrapper))
                ->setId('no hope')
                ->setInstance($instance)
                ->setLastUpdate(new DateTimeImmutable('-1441 minutes'))
        );

        $this->dateTimeService->get('-1 day')
            ->shouldBeCalledOnce()
            ->willReturn($minusOneDay)
        ;

        $this->assertEquals(0, $this->removeOldSessionsCommand->execute());

        $tableManager = $this->serviceManager->get(TableManager::class);
        $chromecastSessionTable = $tableManager->getTable('middleware_chromecast_session');
        $selectMarvin = (new SelectQuery($chromecastSessionTable))
            ->addWhere(new Where('`id`=?', ['marvin']))
        ;
        $selectNoHope = (new SelectQuery($chromecastSessionTable))
            ->addWhere(new Where('`id`=?', ['no hope']))
        ;
        /** @var Client $client */
        $client = $this->serviceManager->get(Client::class);

        $this->assertCount(1, iterator_to_array($client->execute($selectMarvin)->iterateRecords()));
        $this->assertCount(0, iterator_to_array($client->execute($selectNoHope)->iterateRecords()));
    }
}
