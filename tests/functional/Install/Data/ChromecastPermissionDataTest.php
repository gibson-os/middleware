<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Middleware\Install\Data;

use GibsonOS\Core\Dto\Install\Success;
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
        $module = (new Module())->setName('middleware');
        $modelManager->saveWithoutChildren($module);
        $task = (new Task())->setName('chromecast')->setModule($module);
        $modelManager->saveWithoutChildren($task);
        $modelManager->saveWithoutChildren((new Action())->setName('show')->setModule($module)->setTask($task));
        $modelManager->saveWithoutChildren((new Action())->setName('addUser')->setModule($module)->setTask($task));
        $modelManager->saveWithoutChildren((new Action())->setName('toSeeList')->setModule($module)->setTask($task));
        $modelManager->saveWithoutChildren((new Action())->setName('image')->setModule($module)->setTask($task));
        $modelManager->saveWithoutChildren((new Action())->setName('get')->setModule($module)->setTask($task));
        $modelManager->saveWithoutChildren((new Action())->setName('savePosition')->setModule($module)->setTask($task));
        $modelManager->saveWithoutChildren((new Action())->setName('error')->setModule($module)->setTask($task));
    }

    public function testInstall(): void
    {
        $install = $this->chromecastPermissionData->install('galaxy');

        /** @var Success $success */
        $success = $install->current();
        $this->assertEquals('Set chromecast permission for middleware!', $success->getMessage());

        $permissionRepository = $this->serviceManager->get(PermissionRepository::class);

        $this->assertEquals(
            2,
            $permissionRepository->getByModuleTaskAndAction('middleware', 'chromecast', 'show')->getPermission()
        );
        $this->assertEquals(
            4,
            $permissionRepository->getByModuleTaskAndAction('middleware', 'chromecast', 'addUser')->getPermission()
        );
        $this->assertEquals(
            2,
            $permissionRepository->getByModuleTaskAndAction('middleware', 'chromecast', 'toSeeList')->getPermission()
        );
        $this->assertEquals(
            2,
            $permissionRepository->getByModuleTaskAndAction('middleware', 'chromecast', 'image')->getPermission()
        );
        $this->assertEquals(
            2,
            $permissionRepository->getByModuleTaskAndAction('middleware', 'chromecast', 'get')->getPermission()
        );
        $this->assertEquals(
            4,
            $permissionRepository->getByModuleTaskAndAction('middleware', 'chromecast', 'savePosition')->getPermission()
        );
        $this->assertEquals(
            4,
            $permissionRepository->getByModuleTaskAndAction('middleware', 'chromecast', 'error')->getPermission()
        );
    }

    public function testInstallAlreadyExists(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $modelManager->saveWithoutChildren(
            (new Permission())
                ->setModule('middleware')
                ->setTask('chromecast')
                ->setAction('show')
                ->setPermission(1)
        );
        $modelManager->saveWithoutChildren(
            (new Permission())
                ->setModule('middleware')
                ->setTask('chromecast')
                ->setAction('addUser')
                ->setPermission(1)
        );
        $modelManager->saveWithoutChildren(
            (new Permission())
                ->setModule('middleware')
                ->setTask('chromecast')
                ->setAction('toSeeList')
                ->setPermission(1)
        );
        $modelManager->saveWithoutChildren(
            (new Permission())
                ->setModule('middleware')
                ->setTask('chromecast')
                ->setAction('image')
                ->setPermission(1)
        );
        $modelManager->saveWithoutChildren(
            (new Permission())
                ->setModule('middleware')
                ->setTask('chromecast')
                ->setAction('get')
                ->setPermission(1)
        );
        $modelManager->saveWithoutChildren(
            (new Permission())
                ->setModule('middleware')
                ->setTask('chromecast')
                ->setAction('savePosition')
                ->setPermission(1)
        );
        $modelManager->saveWithoutChildren(
            (new Permission())
                ->setModule('middleware')
                ->setTask('chromecast')
                ->setAction('error')
                ->setPermission(1)
        );

        $install = $this->chromecastPermissionData->install('galaxy');

        /** @var Success $success */
        $success = $install->current();
        $this->assertEquals('Set chromecast permission for middleware!', $success->getMessage());

        $permissionRepository = $this->serviceManager->get(PermissionRepository::class);

        $this->assertEquals(
            1,
            $permissionRepository->getByModuleTaskAndAction('middleware', 'chromecast', 'show')->getPermission()
        );
        $this->assertEquals(
            1,
            $permissionRepository->getByModuleTaskAndAction('middleware', 'chromecast', 'addUser')->getPermission()
        );
        $this->assertEquals(
            1,
            $permissionRepository->getByModuleTaskAndAction('middleware', 'chromecast', 'toSeeList')->getPermission()
        );
        $this->assertEquals(
            1,
            $permissionRepository->getByModuleTaskAndAction('middleware', 'chromecast', 'image')->getPermission()
        );
        $this->assertEquals(
            1,
            $permissionRepository->getByModuleTaskAndAction('middleware', 'chromecast', 'get')->getPermission()
        );
        $this->assertEquals(
            1,
            $permissionRepository->getByModuleTaskAndAction('middleware', 'chromecast', 'savePosition')->getPermission()
        );
        $this->assertEquals(
            1,
            $permissionRepository->getByModuleTaskAndAction('middleware', 'chromecast', 'error')->getPermission()
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
