<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Command\Chromecast;

use DateTimeImmutable;
use GibsonOS\Core\Attribute\Install\Cronjob;
use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Exception\Model\DeleteError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Module\Middleware\Repository\SessionRepository;
use JsonException;
use Psr\Log\LoggerInterface;

/**
 * @description Remove old unused Chromecast sessions
 */
#[Cronjob(seconds: '20')]
class RemoveOldSessionsCommand extends AbstractCommand
{
    public function __construct(
        private readonly SessionRepository $sessionRepository,
        private readonly ModelManager $modelManager,
        LoggerInterface $logger,
    ) {
        parent::__construct($logger);
    }

    /**
     * @throws DeleteError
     * @throws SelectError
     * @throws JsonException
     */
    protected function run(): int
    {
        foreach ($this->sessionRepository->getLastUpdateOlderThan(new DateTimeImmutable('-1 day')) as $session) {
            $this->logger->info(sprintf('Remove session "%s"', $session->getId()));
            $this->modelManager->delete($session);
        }

        return self::SUCCESS;
    }
}
