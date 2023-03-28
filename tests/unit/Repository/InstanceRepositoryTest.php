<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Middleware\Repository;

use Codeception\Test\Unit;
use GibsonOS\Module\Middleware\Repository\InstanceRepository;
use mysqlDatabase;
use mysqlRegistry;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class InstanceRepositoryTest extends Unit
{
    use ProphecyTrait;

    private InstanceRepository $instanceRepository;

    private mysqlDatabase|ObjectProphecy $mysqlDatabase;

    protected function _before()
    {
        $this->mysqlDatabase = $this->prophesize(mysqlDatabase::class);
        mysqlRegistry::getInstance()->set('database', $this->mysqlDatabase->reveal());

        $this->instanceRepository = new InstanceRepository();
    }

    public function testGetByToken(): void
    {
        $this->mysqlDatabase->getDatabaseName()
            ->shouldBeCalledOnce()
            ->willReturn('marvin')
        ;
        $this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `marvin`.`middleware_instance`')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchRow()
            ->shouldBeCalledTimes(4)
            ->willReturn(
                ['url', 'varchar(42)', 'NO', '', null, ''],
                ['token', 'varchar(42)', 'NO', '', null, ''],
                ['secret', 'varchar(42)', 'NO', '', null, ''],
                null
            )
        ;
        $this->mysqlDatabase->execute(
            'SELECT `middleware_instance`.`url`, `middleware_instance`.`token`, `middleware_instance`.`secret` FROM `marvin`.`middleware_instance` WHERE `token`=? LIMIT 1',
            ['galaxy'],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'url' => 'zaphod',
                'token' => 'galaxy',
                'secret' => 'bebblebrox',
            ]])
        ;

        $instance = $this->instanceRepository->getByToken('galaxy');

        $this->assertEquals('zaphod', $instance->getUrl());
        $this->assertEquals('galaxy', $instance->getToken());
        $this->assertEquals('bebblebrox', $instance->getSecret());
    }
}
