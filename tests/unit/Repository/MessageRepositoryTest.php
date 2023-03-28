<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Middleware\Repository;

use Codeception\Test\Unit;
use DateTimeImmutable;
use GibsonOS\Core\Enum\Middleware\Message\Type;
use GibsonOS\Module\Middleware\Repository\MessageRepository;
use mysqlDatabase;
use mysqlRegistry;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class MessageRepositoryTest extends Unit
{
    use ProphecyTrait;

    private MessageRepository $messageRepository;

    private mysqlDatabase|ObjectProphecy $mysqlDatabase;

    protected function _before()
    {
        $this->mysqlDatabase = $this->prophesize(mysqlDatabase::class);
        mysqlRegistry::getInstance()->reset();
        mysqlRegistry::getInstance()->set('database', $this->mysqlDatabase->reveal());

        $this->mysqlDatabase->getDatabaseName()
            ->shouldBeCalledOnce()
            ->willReturn('marvin')
        ;
        $this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `marvin`.`middleware_message`')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchRow()
            ->shouldBeCalledTimes(8)
            ->willReturn(
                ['fcmToken', 'varchar(42)', 'NO', '', null, ''],
                ['type', 'varchar(42)', 'NO', '', null, ''],
                ['module', 'varchar(42)', 'NO', '', null, ''],
                ['task', 'varchar(42)', 'NO', '', null, ''],
                ['action', 'varchar(42)', 'NO', '', null, ''],
                ['data', 'varchar(42)', 'NO', '', null, ''],
                ['not_found', 'tinyint(1)', 'NO', '', 0, ''],
                null
            )
        ;

        $this->messageRepository = new MessageRepository('middleware_message');
    }

    public function testGetUnsentMessages(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `middleware_message`.`fcmToken`, `middleware_message`.`type`, `middleware_message`.`module`, `middleware_message`.`task`, `middleware_message`.`action`, `middleware_message`.`data`, `middleware_message`.`not_found` FROM `marvin`.`middleware_message` WHERE `sent` IS NULL ORDER BY `id`',
            [],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'fcmToken' => 'zaphod',
                'module' => 'galaxy',
                'task' => 'bebblebrox',
                'action' => 'arthur',
                'type' => 'UPDATE',
                'data' => '[]',
                'not_found' => 0,
            ]])
        ;

        $message = $this->messageRepository->getUnsentMessages()[0];

        $this->assertEquals('zaphod', $message->getFcmToken());
        $this->assertEquals('galaxy', $message->getModule());
        $this->assertEquals('bebblebrox', $message->getTask());
        $this->assertEquals('arthur', $message->getAction());
        $this->assertEquals(Type::UPDATE, $message->getType());
        $this->assertEquals([], $message->getData());
    }

    public function testFcmTokenNotFound(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `middleware_message`.`fcmToken`, `middleware_message`.`type`, `middleware_message`.`module`, `middleware_message`.`task`, `middleware_message`.`action`, `middleware_message`.`data`, `middleware_message`.`not_found` FROM `marvin`.`middleware_message` WHERE `fcm_token`=? AND `sent` IS NOT NULL ORDER BY `id` DESC LIMIT 1',
            ['galaxy'],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'fcmToken' => 'zaphod',
                'module' => 'galaxy',
                'task' => 'bebblebrox',
                'action' => 'arthur',
                'type' => 'UPDATE',
                'data' => '[]',
                'not_found' => 0,
            ]])
        ;

        $this->assertFalse($this->messageRepository->fcmTokenNotFound('galaxy'));
    }

    public function testFcmTokenFound(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `middleware_message`.`fcmToken`, `middleware_message`.`type`, `middleware_message`.`module`, `middleware_message`.`task`, `middleware_message`.`action`, `middleware_message`.`data`, `middleware_message`.`not_found` FROM `marvin`.`middleware_message` WHERE `fcm_token`=? AND `sent` IS NOT NULL ORDER BY `id` DESC LIMIT 1',
            ['galaxy'],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'fcmToken' => 'zaphod',
                'module' => 'galaxy',
                'task' => 'bebblebrox',
                'action' => 'arthur',
                'type' => 'UPDATE',
                'data' => '[]',
                'not_found' => 1,
            ]])
        ;

        $this->assertTrue($this->messageRepository->fcmTokenNotFound('galaxy'));
    }

    public function testFcmTokenMessageNotFound(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `middleware_message`.`fcmToken`, `middleware_message`.`type`, `middleware_message`.`module`, `middleware_message`.`task`, `middleware_message`.`action`, `middleware_message`.`data`, `middleware_message`.`not_found` FROM `marvin`.`middleware_message` WHERE `fcm_token`=? AND `sent` IS NOT NULL ORDER BY `id` DESC LIMIT 1',
            ['galaxy'],
        )
            ->shouldBeCalledOnce()
            ->willReturn(false)
        ;
        $this->mysqlDatabase->error()
            ->shouldBeCalledOnce()
            ->willReturn('no hope')
        ;

        $this->assertFalse($this->messageRepository->fcmTokenNotFound('galaxy'));
    }

    public function testCountSentMessagesSince(): void
    {
        $date = new DateTimeImmutable();
        $this->mysqlDatabase->execute(
            'SELECT COUNT(`id`) FROM `marvin`.`middleware_message` WHERE `sent`>=?',
            [$date->format('Y-m-d H:i:s')],
        )
            ->shouldBeCalledOnce()
            ->willReturn(false)
        ;
        $this->mysqlDatabase->fetchResult()
            ->shouldBeCalledOnce()
            ->willReturn(42)
        ;

        $this->assertEquals(42, $this->messageRepository->countSentMessagesSince($date));
    }
}
