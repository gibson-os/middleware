<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Command\Message;

use DateTimeImmutable;
use GibsonOS\Core\Attribute\Install\Cronjob;
use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Exception\Flock\LockError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\WebException;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Service\LockService;
use GibsonOS\Core\Utility\StatusCode;
use GibsonOS\Module\Middleware\Exception\FcmException;
use GibsonOS\Module\Middleware\Repository\MessageRepository;
use GibsonOS\Module\Middleware\Service\FcmService;
use JsonException;
use Psr\Log\LoggerInterface;

/**
 * @description Send FCM Messages
 */
#[Cronjob]
class SendCommand extends AbstractCommand
{
    private const LOCK_NAME = 'middlewareSendMessage';

    private const MAX_PER_SECOND = 250;

    private const MAX_PER_HOUR = 5000;

    public function __construct(
        LoggerInterface $logger,
        private readonly FcmService $fcmService,
        private readonly MessageRepository $messageRepository,
        private readonly ModelManager $modelManager,
        private readonly LockService $lockService,
    ) {
        parent::__construct($logger);
    }

    /**
     * @throws SaveError
     * @throws SelectError
     * @throws WebException
     * @throws JsonException
     * @throws FcmException
     */
    protected function run(): int
    {
        try {
            $this->lockService->lock(self::LOCK_NAME);
        } catch (LockError) {
            return self::ERROR;
        }

        foreach ($this->messageRepository->getUnsentMessages() as $unsentMessage) {
            if (
                $this->messageRepository->countSentMessagesSince(new DateTimeImmutable('-1 second')) >= self::MAX_PER_SECOND ||
                $this->messageRepository->countSentMessagesSince(new DateTimeImmutable('-1 hour')) >= self::MAX_PER_HOUR
            ) {
                return self::ERROR;
            }

            try {
                $this->fcmService->pushMessage($unsentMessage);
            } catch (FcmException $exception) {
                if ($exception->getCode() !== StatusCode::NOT_FOUND) {
                    throw $exception;
                }

                $unsentMessage->setNotFound(true);
            }

            $this->modelManager->saveWithoutChildren(
                $unsentMessage
                    ->setSent(new DateTimeImmutable())
                    ->setToken(null)
                    ->setTitle(null)
                    ->setBody(null)
                    ->setData([])
                    ->setVibrate(null)
            );
        }

        $this->lockService->unlock(self::LOCK_NAME);

        return self::SUCCESS;
    }
}
