<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Middleware\Repository;

use Codeception\Test\Unit;
use DateTimeImmutable;
use GibsonOS\Module\Middleware\Model\Message;
use GibsonOS\Module\Middleware\Repository\MessageRepository;
use GibsonOS\Test\Unit\Core\Repository\RepositoryTrait;
use MDO\Dto\Query\Where;
use MDO\Dto\Record;
use MDO\Dto\Value;
use MDO\Enum\OrderDirection;
use MDO\Exception\ClientException;
use MDO\Query\SelectQuery;

class MessageRepositoryTest extends Unit
{
    use RepositoryTrait;

    private MessageRepository $messageRepository;

    protected function _before()
    {
        $this->loadRepository('middleware_message');

        $this->messageRepository = new MessageRepository($this->repositoryWrapper->reveal());
    }

    public function testGetUnsentMessages(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where('`sent` IS NULL', []))
            ->setOrder('`id`')
        ;

        $model = $this->loadModel($selectQuery, Message::class, '');
        $message = $this->messageRepository->getUnsentMessages()[0];
        $date = new DateTimeImmutable();
        $model->setAdded($date);
        $message->setAdded($date);

        $this->assertEquals($model, $message);
    }

    public function testFcmTokenNotFound(): void
    {
        $selectQuery = (new SelectQuery($this->table, 't'))
            ->addWhere(new Where('`fcm_token`=? AND `sent` IS NOT NULL', ['galaxy']))
            ->setOrder('`id`', OrderDirection::DESC)
            ->setLimit(1)
        ;

        $this->loadModel($selectQuery, Message::class)->setNotFound(true);
        $this->assertTrue($this->messageRepository->fcmTokenNotFound('galaxy'));
    }

    public function testFcmTokenFound(): void
    {
        $selectQuery = (new SelectQuery($this->table, 't'))
            ->addWhere(new Where('`fcm_token`=? AND `sent` IS NOT NULL', ['galaxy']))
            ->setOrder('`id`', OrderDirection::DESC)
            ->setLimit(1)
        ;

        $this->loadModel($selectQuery, Message::class)
            ->setNotFound(false)
            ->setAdded(new DateTimeImmutable())
        ;
        $this->assertFalse($this->messageRepository->fcmTokenNotFound('galaxy'));
    }

    public function testFcmTokenMessageNotFound(): void
    {
        $selectQuery = (new SelectQuery($this->table, 't'))
            ->addWhere(new Where('`fcm_token`=? AND `sent` IS NOT NULL', ['galaxy']))
            ->setOrder('`id`', OrderDirection::DESC)
            ->setLimit(1)
        ;

        $this->repositoryWrapper->getModelWrapper()
            ->shouldBeCalledOnce()
            ->willReturn($this->modelWrapper)
        ;
        $this->tableManager->getTable($this->table->getTableName())
            ->shouldBeCalledOnce()
            ->willReturn($this->table)
        ;
        $this->repositoryWrapper->getTableManager()
            ->shouldBeCalledOnce()
            ->willReturn($this->tableManager)
        ;
        $this->childrenQuery->extend($selectQuery, Message::class, [])
            ->willReturn($selectQuery)
        ;
        $this->repositoryWrapper->getChildrenQuery()
            ->shouldBeCalledOnce()
            ->willReturn($this->childrenQuery->reveal())
        ;
        $this->client->execute($selectQuery)
            ->shouldBeCalledOnce()
            ->willThrow(ClientException::class)
        ;
        $this->repositoryWrapper->getClient()
            ->shouldBeCalledOnce()
            ->willReturn($this->client->reveal())
        ;

        $this->assertFalse($this->messageRepository->fcmTokenNotFound('galaxy'));
    }

    public function testCountSentMessagesSince(): void
    {
        $date = new DateTimeImmutable();
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where('`sent`>=?', [$date->format('Y-m-d H:i:s')]))
            ->setSelects(['count' => 'COUNT(`id`)'])
        ;
        $this->loadAggregation($selectQuery, new Record(['count' => new Value(42)]));

        $this->assertEquals(42, $this->messageRepository->countSentMessagesSince($date));
    }
}
