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
use GibsonOS\Module\Middleware\Install\Data\ChromecastPermissionData;
use GibsonOS\Test\Functional\Middleware\MiddlewareFunctionalTest;

class ChromecastPermissionDataTest extends MiddlewareFunctionalTest
{
    private ChromecastPermissionData $chromecastPermissionData;

    protected function _before(): void
    {
        parent::_before();

        $this->chromecastPermissionData = $this->serviceManager->get(ChromecastPermissionData::class);

        $modelManager = $this->serviceManager->get(ModelManager::class);
        $module = (new Module($this->modelWrapper))->setName('middleware');
        $modelManager->saveWithoutChildren($module);
        $task = (new Task($this->modelWrapper))->setName('chromecast')->setModule($module);
        $modelManager->saveWithoutChildren($task);
        $modelManager->saveWithoutChildren(
            (new Action($this->modelWrapper))
                ->setName('show')
                ->setMethod(HttpMethod::GET)
                ->setModule($module)
                ->setTask($task)
        );
        $modelManager->saveWithoutChildren(
            (new Action($this->modelWrapper))
                ->setName('user')
                ->setMethod(HttpMethod::POST)
                ->setModule($module)
                ->setTask($task)
        );
        $modelManager->saveWithoutChildren(
            (new Action($this->modelWrapper))
                ->setName('toSeeList')
                ->setMethod(HttpMethod::GET)
                ->setModule($module)
                ->setTask($task)
        );
        $modelManager->saveWithoutChildren(
            (new Action($this->modelWrapper))
                ->setName('image')
                ->setMethod(HttpMethod::GET)
                ->setModule($module)
                ->setTask($task)
        );
        $modelManager->saveWithoutChildren(
            (new Action($this->modelWrapper))
                ->setName('')
                ->setMethod(HttpMethod::GET)
                ->setModule($module)
                ->setTask($task)
        );
        $modelManager->saveWithoutChildren(
            (new Action($this->modelWrapper))
                ->setName('position')
                ->setMethod(HttpMethod::POST)
                ->setModule($module)
                ->setTask($task)
        );
        $modelManager->saveWithoutChildren(
            (new Action($this->modelWrapper))
                ->setName('error')
                ->setMethod(HttpMethod::POST)
                ->setModule($module)
                ->setTask($task)
        );
        $modelManager->saveWithoutChildren(
            (new Action($this->modelWrapper))
                ->setName('stream')
                ->setMethod(HttpMethod::GET)
                ->setModule($module)
                ->setTask($task)
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
            ->setName('chromecast')
            ->setModule($module)
        ;
        $modelManager->saveWithoutChildren($task);
        $showAction = (new Action($this->modelWrapper))
            ->setName('show')
            ->setMethod(HttpMethod::GET)
            ->setModule($module)
            ->setTask($task)
        ;
        $modelManager->saveWithoutChildren($showAction);
        $userAction = (new Action($this->modelWrapper))
            ->setName('user')
            ->setMethod(HttpMethod::POST)
            ->setModule($module)
            ->setTask($task)
        ;
        $modelManager->saveWithoutChildren($userAction);
        $toSeeListAction = (new Action($this->modelWrapper))
            ->setName('toSeeList')
            ->setMethod(HttpMethod::GET)
            ->setModule($module)
            ->setTask($task)
        ;
        $modelManager->saveWithoutChildren($toSeeListAction);
        $imageAction = (new Action($this->modelWrapper))
            ->setName('image')
            ->setMethod(HttpMethod::GET)
            ->setModule($module)
            ->setTask($task)
        ;
        $modelManager->saveWithoutChildren($imageAction);
        $getAction = (new Action($this->modelWrapper))
            ->setName('')
            ->setMethod(HttpMethod::GET)
            ->setModule($module)
            ->setTask($task)
        ;
        $modelManager->saveWithoutChildren($getAction);
        $positionAction = (new Action($this->modelWrapper))
            ->setName('position')
            ->setMethod(HttpMethod::POST)
            ->setModule($module)
            ->setTask($task)
        ;
        $modelManager->saveWithoutChildren($positionAction);
        $errorAction = (new Action($this->modelWrapper))
            ->setName('error')
            ->setMethod(HttpMethod::POST)
            ->setModule($module)
            ->setTask($task)
        ;
        $modelManager->saveWithoutChildren($errorAction);
        $streamAction = (new Action($this->modelWrapper))
            ->setName('stream')
            ->setMethod(HttpMethod::GET)
            ->setModule($module)
            ->setTask($task)
        ;
        $modelManager->saveWithoutChildren($streamAction);

        $install = $this->chromecastPermissionData->install('galaxy');

        /** @var Success $success */
        $success = $install->current();
        $this->assertEquals('Set chromecast permission for middleware!', $success->getMessage());

        $permissionRepository = $this->serviceManager->get(PermissionRepository::class);

        $this->assertEquals(
            2,
            $permissionRepository->getByModuleTaskAndAction($module, $task, $showAction)->getPermission()
        );
        $this->assertEquals(
            4,
            $permissionRepository->getByModuleTaskAndAction($module, $task, $userAction)->getPermission()
        );
        $this->assertEquals(
            2,
            $permissionRepository->getByModuleTaskAndAction($module, $task, $toSeeListAction)->getPermission()
        );
        $this->assertEquals(
            2,
            $permissionRepository->getByModuleTaskAndAction($module, $task, $imageAction)->getPermission()
        );
        $this->assertEquals(
            2,
            $permissionRepository->getByModuleTaskAndAction($module, $task, $getAction)->getPermission()
        );
        $this->assertEquals(
            4,
            $permissionRepository->getByModuleTaskAndAction($module, $task, $positionAction)->getPermission()
        );
        $this->assertEquals(
            4,
            $permissionRepository->getByModuleTaskAndAction($module, $task, $errorAction)->getPermission()
        );
        $this->assertEquals(
            2,
            $permissionRepository->getByModuleTaskAndAction($module, $task, $streamAction)->getPermission()
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
            ->setName('chromecast')
            ->setModule($module)
        ;
        $modelManager->saveWithoutChildren($task);
        $showAction = (new Action($this->modelWrapper))
            ->setName('show')
            ->setMethod(HttpMethod::GET)
            ->setModule($module)
            ->setTask($task)
        ;
        $modelManager->saveWithoutChildren($showAction);
        $userAction = (new Action($this->modelWrapper))
            ->setName('user')
            ->setMethod(HttpMethod::POST)
            ->setModule($module)
            ->setTask($task)
        ;
        $modelManager->saveWithoutChildren($userAction);
        $toSeeListAction = (new Action($this->modelWrapper))
            ->setName('toSeeList')
            ->setMethod(HttpMethod::GET)
            ->setModule($module)
            ->setTask($task)
        ;
        $modelManager->saveWithoutChildren($toSeeListAction);
        $imageAction = (new Action($this->modelWrapper))
            ->setName('image')
            ->setMethod(HttpMethod::GET)
            ->setModule($module)
            ->setTask($task)
        ;
        $modelManager->saveWithoutChildren($imageAction);
        $getAction = (new Action($this->modelWrapper))
            ->setName('')
            ->setMethod(HttpMethod::GET)
            ->setModule($module)
            ->setTask($task)
        ;
        $modelManager->saveWithoutChildren($getAction);
        $positionAction = (new Action($this->modelWrapper))
            ->setName('position')
            ->setMethod(HttpMethod::POST)
            ->setModule($module)
            ->setTask($task)
        ;
        $modelManager->saveWithoutChildren($positionAction);
        $errorAction = (new Action($this->modelWrapper))
            ->setName('error')
            ->setMethod(HttpMethod::POST)
            ->setModule($module)
            ->setTask($task)
        ;
        $modelManager->saveWithoutChildren($errorAction);
        $streamAction = (new Action($this->modelWrapper))
            ->setName('stream')
            ->setMethod(HttpMethod::GET)
            ->setModule($module)
            ->setTask($task)
        ;
        $modelManager->saveWithoutChildren($streamAction);
        $modelManager->saveWithoutChildren(
            (new Permission($this->modelWrapper))
                ->setModule($module)
                ->setTask($task)
                ->setAction($showAction)
                ->setPermission(1)
        );
        $modelManager->saveWithoutChildren(
            (new Permission($this->modelWrapper))
                ->setModule($module)
                ->setTask($task)
                ->setAction($userAction)
                ->setPermission(1)
        );
        $modelManager->saveWithoutChildren(
            (new Permission($this->modelWrapper))
                ->setModule($module)
                ->setTask($task)
                ->setAction($toSeeListAction)
                ->setPermission(1)
        );
        $modelManager->saveWithoutChildren(
            (new Permission($this->modelWrapper))
                ->setModule($module)
                ->setTask($task)
                ->setAction($imageAction)
                ->setPermission(1)
        );
        $modelManager->saveWithoutChildren(
            (new Permission($this->modelWrapper))
                ->setModule($module)
                ->setTask($task)
                ->setAction($getAction)
                ->setPermission(1)
        );
        $modelManager->saveWithoutChildren(
            (new Permission($this->modelWrapper))
                ->setModule($module)
                ->setTask($task)
                ->setAction($positionAction)
                ->setPermission(1)
        );
        $modelManager->saveWithoutChildren(
            (new Permission($this->modelWrapper))
                ->setModule($module)
                ->setTask($task)
                ->setAction($errorAction)
                ->setPermission(1)
        );
        $modelManager->saveWithoutChildren(
            (new Permission($this->modelWrapper))
                ->setModule($module)
                ->setTask($task)
                ->setAction($streamAction)
                ->setPermission(1)
        );

        $install = $this->chromecastPermissionData->install('galaxy');

        /** @var Success $success */
        $success = $install->current();
        $this->assertEquals('Set chromecast permission for middleware!', $success->getMessage());

        $permissionRepository = $this->serviceManager->get(PermissionRepository::class);

        $this->assertEquals(
            1,
            $permissionRepository->getByModuleTaskAndAction($module, $task, $showAction)->getPermission()
        );
        $this->assertEquals(
            1,
            $permissionRepository->getByModuleTaskAndAction($module, $task, $userAction)->getPermission()
        );
        $this->assertEquals(
            1,
            $permissionRepository->getByModuleTaskAndAction($module, $task, $toSeeListAction)->getPermission()
        );
        $this->assertEquals(
            1,
            $permissionRepository->getByModuleTaskAndAction($module, $task, $imageAction)->getPermission()
        );
        $this->assertEquals(
            1,
            $permissionRepository->getByModuleTaskAndAction($module, $task, $getAction)->getPermission()
        );
        $this->assertEquals(
            1,
            $permissionRepository->getByModuleTaskAndAction($module, $task, $positionAction)->getPermission()
        );
        $this->assertEquals(
            1,
            $permissionRepository->getByModuleTaskAndAction($module, $task, $errorAction)->getPermission()
        );
        $this->assertEquals(
            1,
            $permissionRepository->getByModuleTaskAndAction($module, $task, $streamAction)->getPermission()
        );
    }

    public function testGetPart(): void
    {
        $this->assertEquals('data', $this->chromecastPermissionData->getPart());
    }

    public function testGetPriority(): void
    {
        $this->assertEquals(0, $this->chromecastPermissionData->getPriority());
    }

    public function testGetModule(): void
    {
        $this->assertEquals('middleware', $this->chromecastPermissionData->getModule());
    }
}
