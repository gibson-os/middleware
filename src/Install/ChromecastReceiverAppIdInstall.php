<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Install;

use Generator;
use GibsonOS\Core\Dto\Install\Configuration;
use GibsonOS\Core\Install\AbstractInstall;
use GibsonOS\Core\Install\SingleInstallInterface;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;
use Override;

class ChromecastReceiverAppIdInstall extends AbstractInstall implements PriorityInterface, SingleInstallInterface
{
    #[Override]
    public function install(string $module): Generator
    {
        yield $receiverAppIdIdInput = $this->getEnvInput(
            'CHROMECAST_RECEIVER_APP_ID',
            'What is the Chromecast receiver app id?',
        );

        yield (new Configuration('Chromecast configuration generated!'))
            ->setValue('CHROMECAST_RECEIVER_APP_ID', $receiverAppIdIdInput->getValue() ?? '')
        ;
    }

    #[Override]
    public function getPart(): string
    {
        return InstallService::PART_CONFIG;
    }

    #[Override]
    public function getPriority(): int
    {
        return 0;
    }
}
