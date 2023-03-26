<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Install\Data;

use Generator;
use GibsonOS\Core\Dto\Install\Success;
use GibsonOS\Core\Install\AbstractInstall;
use GibsonOS\Core\Install\SingleInstallInterface;
use GibsonOS\Core\Model\Role;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;

class MiddlewareRoleData extends AbstractInstall implements PriorityInterface, SingleInstallInterface
{
    public function install(string $module): Generator
    {
        $permission = (new Role\Permission())
            ->setModule('middleware')
            ->setPermission(Permission::READ + Permission::WRITE)
        ;
        $this->modelManager->save(
            (new Role())
                ->setName('Middleware')
                ->addPermissions([
                    $permission,
                ])
        );
        $this->modelManager->save($permission);

        yield new Success('Add middleware role!');
    }

    public function getPart(): string
    {
        return InstallService::PART_DATA;
    }

    public function getPriority(): int
    {
        return 0;
    }
}
