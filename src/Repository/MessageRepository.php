<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Repository;

use DateTimeInterface;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Module\Middleware\Model\Message;
use JsonException;
use MDO\Enum\OrderDirection;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionException;

class MessageRepository extends AbstractRepository
{
    /**
     * @throws JsonException
     * @throws ClientException
     * @throws RecordException
     * @throws ReflectionException
     *
     * @return Message[]
     */
    public function getUnsentMessages(): array
    {
        return $this->fetchAll(
            '`sent` IS NULL',
            [],
            Message::class,
            orderBy: ['`id`' => OrderDirection::ASC],
        );
    }

    /**
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     */
    public function fcmTokenNotFound(string $fcmToken): bool
    {
        try {
            return $this->fetchOne(
                '`fcm_token`=? AND `sent` IS NOT NULL',
                [$fcmToken],
                Message::class,
                ['`id`' => OrderDirection::DESC],
            )->isNotFound();
        } catch (ClientException|SelectError) {
            return false;
        }
    }

    /**
     * @throws ClientException
     * @throws RecordException
     * @throws SelectError
     */
    public function countSentMessagesSince(DateTimeInterface $dateTime): int
    {
        $aggregations = $this->getAggregations(
            ['count' => 'COUNT(`id`)'],
            Message::class,
            '`sent`>=?',
            [$dateTime->format('Y-m-d H:i:s')],
        );

        return (int) $aggregations->get('count')->getValue();
    }
}
