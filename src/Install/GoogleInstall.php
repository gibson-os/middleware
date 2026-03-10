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

class GoogleInstall extends AbstractInstall implements PriorityInterface, SingleInstallInterface
{
    #[Override]
    public function install(string $module): Generator
    {
        yield $googleApplicationCredentialsInput = $this->getEnvInput(
            'GOOGLE_APPLICATION_CREDENTIALS',
            'What is the path of the google application credentials?',
        );
        yield $fcmProjectIdInput = $this->getEnvInput(
            'FCM_PROJECT_ID',
            'What is the FCM project id?',
        );

        yield (new Configuration('Google application configuration generated!'))
            ->setValue('GOOGLE_APPLICATION_CREDENTIALS', $googleApplicationCredentialsInput->getValue() ?? '')
            ->setValue('FCM_PROJECT_ID', $fcmProjectIdInput->getValue() ?? '')
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
        return 800;
    }
}
