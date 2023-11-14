<?php
declare(strict_types=1);

namespace GibsonOS\Test\Functional\Middleware\Install\Data;

use GibsonOS\Core\Dto\Install\Success;
use GibsonOS\Core\Enum\HttpMethod;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\Action;
use GibsonOS\Core\Model\Module;
use GibsonOS\Core\Model\Task;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Repository\User\PermissionRepository;
use GibsonOS\Module\Middleware\Install\Data\InstancePermissionData;
use GibsonOS\Test\Functional\Middleware\MiddlewareFunctionalTest;

class InstancePermissionDataTest extends MiddlewareFunctionalTest
{
    private InstancePermissionData $instancePermissionData;

    protected function _before(): void
    {
        parent::_before();

        $this->instancePermissionData = $this->serviceManager->get(InstancePermissionData::class);

        $modelManager = $this->serviceManager->get(ModelManager::class);
        $module = (new Module($this->modelWrapper))->setName('middleware');
        $modelManager->saveWithoutChildren($module);
        $task = (new Task($this->modelWrapper))->setName('instance')->setModule($module);
        $modelManager->saveWithoutChildren($task);
        $modelManager->saveWithoutChildren(
            (new Action($this->modelWrapper))
                ->setName('newToken')
                ->setModule($module)->setTask($task)
                ->setMethod(HttpMethod::GET)
        );
    }

    public function testInstall(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $module = (new Module($this->modelWrapper))
            ->setName('middleware')
        ;
        $modelManager->saveWithoutChildren($module);
        $task = (new Task($this->modelWrapper))
            ->setName('instance')
            ->setModule($module)
        ;
        $modelManager->saveWithoutChildren($task);
        $action = (new Action($this->modelWrapper))
            ->setName('newToken')
            ->setMethod(HttpMethod::POST)
            ->setModule($module)
            ->setTask($task)
        ;
        $modelManager->saveWithoutChildren($action);

        $install = $this->instancePermissionData->install('galaxy');

        /** @var Success $success */
        $success = $install->current();
        $this->assertEquals('Set instance permission for middleware!', $success->getMessage());

        $permissionRepository = $this->serviceManager->get(PermissionRepository::class);

        $this->assertEquals(
            4,
            $permissionRepository->getByModuleTaskAndAction($module, $task, $action)->getPermission()
        );
    }

    public function testInstallAlreadyExists(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $module = (new Module($this->modelWrapper))
            ->setName('middleware')
        ;
        $modelManager->saveWithoutChildren($module);
        $task = (new Task($this->modelWrapper))
            ->setName('instance')
            ->setModule($module)
        ;
        $modelManager->saveWithoutChildren($task);
        $action = (new Action($this->modelWrapper))
            ->setName('newToken')
            ->setMethod(HttpMethod::POST)
            ->setModule($module)
            ->setTask($task)
        ;
        $modelManager->saveWithoutChildren($action);
        $modelManager->saveWithoutChildren(
            (new Permission($this->modelWrapper))
                ->setModule($module)
                ->setTask($task)
                ->setAction($action)
                ->setPermission(1)
        );

        $install = $this->instancePermissionData->install('galaxy');

        /** @var Success $success */
        $success = $install->current();
        $this->assertEquals('Set instance permission for middleware!', $success->getMessage());

        $permissionRepository = $this->serviceManager->get(PermissionRepository::class);

        $this->assertEquals(
            1,
            $permissionRepository->getByModuleTaskAndAction($module, $task, $action)->getPermission(),
        );
    }

    public function testGetPart(): void
    {
        $this->assertEquals('data', $this->instancePermissionData->getPart());
    }

    public function testGetPriority(): void
    {
        $this->assertEquals(0, $this->instancePermissionData->getPriority());
    }

    public function testGetModule(): void
    {
        $this->assertEquals('middleware', $this->instancePermissionData->getModule());
    }
}
