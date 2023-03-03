<?php
declare(strict_types=1);

namespace GibsonOS\Middleware\Install\Data;

use GibsonOS\Core\Dto\Install\Success;
use GibsonOS\Core\Install\AbstractInstall;
use GibsonOS\Core\Model\Role;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;

class MiddlewareRoleData extends AbstractInstall implements PriorityInterface
{
    public function install(string $module): \Generator
    {
        $this->modelManager->save(
            (new Role())
                ->setName('Middleware')
                ->addPermissions([
                    (new Role\Permission())
                        ->setModule('middleware')
                        ->setPermission(Permission::READ + Permission::WRITE),
                ])
        );

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
