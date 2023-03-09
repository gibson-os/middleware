<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Install\Data;

use GibsonOS\Core\Dto\Install\Success;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Install\AbstractInstall;
use GibsonOS\Core\Install\SingleInstallInterface;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Repository\User\PermissionRepository;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;

class ChromecastPermissionData extends AbstractInstall implements PriorityInterface, SingleInstallInterface
{
    public function __construct(
        ServiceManager $serviceManagerService,
        private readonly PermissionRepository $permissionRepository,
    ) {
        parent::__construct($serviceManagerService);
    }

    /**
     * @throws SaveError
     * @throws \JsonException
     * @throws \ReflectionException
     */
    public function install(string $module): \Generator
    {
        try {
            $this->permissionRepository->getByModuleTaskAndAction('middleware', 'chromecast', 'show');
        } catch (SelectError) {
            $this->modelManager->save(
                (new Permission())
                    ->setModule('middleware')
                    ->setTask('chromecast')
                    ->setAction('show')
                    ->setPermission(Permission::READ)
            );
        }

        yield new Success('Set chromecast permission for middleware!');
    }

    public function getPart(): string
    {
        return InstallService::PART_DATA;
    }

    public function getModule(): ?string
    {
        return 'middleware';
    }

    public function getPriority(): int
    {
        return 0;
    }
}
