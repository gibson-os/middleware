<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Install\Data;

use Generator;
use GibsonOS\Core\Dto\Install\Success;
use GibsonOS\Core\Enum\HttpMethod;
use GibsonOS\Core\Enum\Permission as PermissionEnum;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Install\AbstractInstall;
use GibsonOS\Core\Install\SingleInstallInterface;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Repository\ActionRepository;
use GibsonOS\Core\Repository\TaskRepository;
use GibsonOS\Core\Repository\User\PermissionRepository;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionException;

class InstancePermissionData extends AbstractInstall implements PriorityInterface, SingleInstallInterface
{
    public function __construct(
        ServiceManager $serviceManagerService,
        private readonly PermissionRepository $permissionRepository,
        private readonly TaskRepository $taskRepository,
        private readonly ActionRepository $actionRepository,
    ) {
        parent::__construct($serviceManagerService);
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws SaveError
     * @throws SelectError
     * @throws ClientException
     * @throws RecordException
     */
    public function install(string $module): Generator
    {
        $middlewareModule = $this->moduleRepository->getByName('middleware');
        $instanceTask = $this->taskRepository->getByNameAndModuleId('instance', $middlewareModule->getId() ?? 0);
        $newTokenAction = $this->actionRepository->getByNameAndTaskId('newToken', HttpMethod::POST, $instanceTask->getId() ?? 0);

        try {
            $this->permissionRepository->getByModuleTaskAndAction($middlewareModule, $instanceTask, $newTokenAction);
        } catch (SelectError) {
            $this->modelManager->save(
                (new Permission($this->modelWrapper))
                    ->setModule($middlewareModule)
                    ->setTask($instanceTask)
                    ->setAction($newTokenAction)
                    ->setPermission(PermissionEnum::WRITE->value),
            );
        }

        yield new Success('Set instance permission for middleware!');
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
