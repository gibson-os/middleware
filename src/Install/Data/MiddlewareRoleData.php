<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Install\Data;

use Generator;
use GibsonOS\Core\Dto\Install\Success;
use GibsonOS\Core\Enum\Permission;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Install\AbstractInstall;
use GibsonOS\Core\Install\SingleInstallInterface;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Model\Role;
use GibsonOS\Core\Repository\RoleRepository;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionException;

class MiddlewareRoleData extends AbstractInstall implements PriorityInterface, SingleInstallInterface
{
    public function __construct(
        ServiceManager $serviceManagerService,
        private readonly RoleRepository $roleRepository,
    ) {
        parent::__construct($serviceManagerService);
    }

    /**
     * @throws SelectError
     * @throws SaveError
     * @throws JsonException
     * @throws ClientException
     * @throws RecordException
     * @throws ReflectionException
     */
    public function install(string $module): Generator
    {
        try {
            $this->roleRepository->getByName('Middleware');
        } catch (SelectError) {
            $permission = (new Role\Permission($this->modelWrapper))
                ->setModule($this->moduleRepository->getByName('middleware'))
                ->setPermission(Permission::READ->value + Permission::WRITE->value)
            ;
            $this->modelManager->save(
                (new Role($this->modelWrapper))
                    ->setName('Middleware')
                    ->addPermissions([
                        $permission,
                    ]),
            );
        }

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
