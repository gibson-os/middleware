<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Middleware\Repository\Chromecast\Session;

use Codeception\Test\Unit;
use GibsonOS\Module\Middleware\Model\Chromecast\Session;
use GibsonOS\Module\Middleware\Repository\Chromecast\Session\UserRepository;
use mysqlDatabase;
use mysqlRegistry;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class UserRepositoryTest extends Unit
{
    use ProphecyTrait;

    private UserRepository $userRepository;

    private mysqlDatabase|ObjectProphecy $mysqlDatabase;

    protected function _before()
    {
        $this->mysqlDatabase = $this->prophesize(mysqlDatabase::class);
        mysqlRegistry::getInstance()->reset();
        mysqlRegistry::getInstance()->set('database', $this->mysqlDatabase->reveal());

        $this->userRepository = new UserRepository();
    }

    public function testGetFirst(): void
    {
        $this->mysqlDatabase->getDatabaseName()
            ->shouldBeCalledOnce()
            ->willReturn('marvin')
        ;
        $this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `marvin`.`middleware_chromecast_session_user`')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchRow()
            ->shouldBeCalledTimes(2)
            ->willReturn(
                ['user_id', 'bigint(42)', 'NO', '', null, ''],
                null
            )
        ;
        $this->mysqlDatabase->execute(
            'SELECT `middleware_chromecast_session_user`.`user_id` FROM `marvin`.`middleware_chromecast_session_user` WHERE `session_id`=? ORDER BY `added` LIMIT 1',
            ['galaxy'],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'user_id' => 42,
            ]])
        ;

        $session = (new Session())->setId('galaxy');

        $this->assertEquals(42, $this->userRepository->getFirst($session)->getUserId());
    }
}
