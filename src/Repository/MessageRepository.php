<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Repository;

use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Module\Middleware\Model\Message;

class MessageRepository extends AbstractRepository
{
    public function __construct(#[GetTableName(Message::class)] private readonly string $messageTableName)
    {
    }

    /**
     * @throws SelectError
     *
     * @return Message[]
     */
    public function getUnsentMessages(): array
    {
        return $this->fetchAll('`sent` IS NULL', [], Message::class, orderBy: '`id`');
    }

    public function fcmTokenNotFound(string $fcmToken): bool
    {
        try {
            return $this->fetchOne(
                '`fcm_token`=? AND `sent` IS NOT NULL',
                [$fcmToken],
                Message::class,
                '`id` DESC'
            )->isNotFound();
        } catch (SelectError) {
            return false;
        }
    }

    public function countSentMessagesSince(\DateTimeInterface $dateTime): int
    {
        $table = $this->getTable($this->messageTableName)
            ->setWhere('`sent`>=?')
            ->addWhereParameter($dateTime->format('Y-m-d H:i:s'))
        ;
        $table->selectPrepared(false, 'COUNT(`id`)');

        return (int) $table->connection->fetchResult();
    }
}
