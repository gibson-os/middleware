<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Command\Chromecast;

use GibsonOS\Core\Attribute\Command\Lock;
use GibsonOS\Core\Attribute\Install\Cronjob;
use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Exception\Model\DeleteError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Module\Middleware\Repository\Chromecast\SessionRepository;
use JsonException;
use Psr\Log\LoggerInterface;

/**
 * @description Remove old unused Chromecast sessions
 */
#[Cronjob(seconds: '20')]
#[Lock('middlewareChromecastRemoveOldSessionCommand')]
class RemoveOldSessionsCommand extends AbstractCommand
{
    public function __construct(
        private readonly SessionRepository $sessionRepository,
        private readonly ModelManager $modelManager,
        private readonly DateTimeService $dateTimeService,
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
        foreach ($this->sessionRepository->getLastUpdateOlderThan($this->dateTimeService->get('-1 day')) as $session) {
            $this->logger->info(sprintf('Remove session "%s"', $session->getId()));
            $this->modelManager->delete($session);
        }

        return self::SUCCESS;
    }
}
