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
use GibsonOS\Core\Model\Module;
use GibsonOS\Core\Model\Task;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Repository\ActionRepository;
use GibsonOS\Core\Repository\TaskRepository;
use GibsonOS\Core\Repository\User\PermissionRepository;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;
use ReflectionException;

class ChromecastPermissionData extends AbstractInstall implements PriorityInterface, SingleInstallInterface
{
    private Module $module;

    private Task $task;

    public function __construct(
        ServiceManager $serviceManagerService,
        private readonly PermissionRepository $permissionRepository,
        private readonly TaskRepository $taskRepository,
        private readonly ActionRepository $actionRepository,
    ) {
        parent::__construct($serviceManagerService);
    }

    /**
     * @throws ReflectionException
     * @throws SaveError
     * @throws SelectError
     */
    public function install(string $module): Generator
    {
        $this->module = $this->moduleRepository->getByName('middleware');
        $this->task = $this->taskRepository->getByNameAndModuleId('chromecast', $this->module->getId() ?? 0);

        $this->setPermission('show', HttpMethod::GET, PermissionEnum::READ);
        $this->setPermission('user', HttpMethod::POST, PermissionEnum::WRITE);
        $this->setPermission('toSeeList', HttpMethod::GET, PermissionEnum::READ);
        $this->setPermission('image', HttpMethod::GET, PermissionEnum::READ);
        $this->setPermission('', HttpMethod::GET, PermissionEnum::READ);
        $this->setPermission('position', HttpMethod::POST, PermissionEnum::WRITE);
        $this->setPermission('error', HttpMethod::POST, PermissionEnum::WRITE);
        $this->setPermission('video', HttpMethod::GET, PermissionEnum::READ);
        $this->setPermission('audio', HttpMethod::GET, PermissionEnum::READ);

        yield new Success('Set chromecast permission for middleware!');
    }

    /**
     * @throws SaveError
     * @throws ReflectionException
     * @throws SelectError
     */
    private function setPermission(string $action, HttpMethod $method, PermissionEnum $permission): void
    {
        $actionModel = $this->actionRepository->getByNameAndTaskId($action, $method, $this->task->getId() ?? 0);

        try {
            $this->permissionRepository->getByModuleTaskAndAction(
                $this->module,
                $this->task,
                $actionModel,
            );
        } catch (SelectError) {
            $this->modelManager->save(
                (new Permission())
                    ->setModule($this->module)
                    ->setTask($this->task)
                    ->setAction($actionModel)
                    ->setPermission($permission->value)
            );
        }
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
