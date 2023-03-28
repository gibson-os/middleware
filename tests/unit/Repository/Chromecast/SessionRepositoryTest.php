<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Middleware\Repository\Chromecast;

use Codeception\Test\Unit;
use DateTimeImmutable;
use GibsonOS\Module\Middleware\Repository\Chromecast\SessionRepository;
use mysqlDatabase;
use mysqlRegistry;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class SessionRepositoryTest extends Unit
{
    use ProphecyTrait;

    private SessionRepository $sessionRepository;

    private mysqlDatabase|ObjectProphecy $mysqlDatabase;

    protected function _before()
    {
        $this->mysqlDatabase = $this->prophesize(mysqlDatabase::class);
        mysqlRegistry::getInstance()->reset();
        mysqlRegistry::getInstance()->set('database', $this->mysqlDatabase->reveal());

        $this->sessionRepository = new SessionRepository();
    }

    public function testGetLastUpdateOlderThan(): void
    {
        $this->mysqlDatabase->getDatabaseName()
            ->shouldBeCalledOnce()
            ->willReturn('marvin')
        ;
        $this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `marvin`.`middleware_chromecast_session`')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchRow()
            ->shouldBeCalledTimes(2)
            ->willReturn(
                ['instance_id', 'bigint(42)', 'NO', '', null, ''],
                null
            )
        ;
        $date = new DateTimeImmutable();
        $this->mysqlDatabase->execute(
            'SELECT `middleware_chromecast_session`.`instance_id` FROM `marvin`.`middleware_chromecast_session` WHERE `last_update`<?',
            [$date->format('Y-m-d H:i:s')],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'instance_id' => 42,
            ]])
        ;

        $this->assertEquals(42, $this->sessionRepository->getLastUpdateOlderThan($date)[0]->getInstanceId());
    }
}
