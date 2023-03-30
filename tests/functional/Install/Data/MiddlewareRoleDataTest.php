<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Middleware\Install\Data;

use GibsonOS\Core\Dto\Install\Success;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\Module;
use GibsonOS\Core\Model\Role;
use GibsonOS\Core\Repository\RoleRepository;
use GibsonOS\Module\Middleware\Install\Data\MiddlewareRoleData;
use GibsonOS\Test\Functional\Middleware\MiddlewareFunctionalTest;

class MiddlewareRoleDataTest extends MiddlewareFunctionalTest
{
    private MiddlewareRoleData $middlewareRoleData;

    protected function _before(): void
    {
        parent::_before();

        $this->middlewareRoleData = $this->serviceManager->get(MiddlewareRoleData::class);

        $modelManager = $this->serviceManager->get(ModelManager::class);
        $modelManager->saveWithoutChildren((new Module())->setName('middleware'));
    }

    public function testInstall(): void
    {
        $install = $this->middlewareRoleData->install('galaxy');

        /** @var Success $success */
        $success = $install->current();
        $this->assertEquals('Add middleware role!', $success->getMessage());

        $roleRepository = $this->serviceManager->get(RoleRepository::class);
        $role = $roleRepository->getByName('Middleware');
        $this->assertEquals('middleware', $role->getPermissions()[0]->getModule());
        $this->assertEquals(6, $role->getPermissions()[0]->getPermission());
    }

    public function testInstallAlreadyExists(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $modelManager->saveWithoutChildren((new Role())->setName('Middleware'));

        $install = $this->middlewareRoleData->install('galaxy');

        /** @var Success $success */
        $success = $install->current();
        $this->assertEquals('Add middleware role!', $success->getMessage());

        $roleRepository = $this->serviceManager->get(RoleRepository::class);
        $this->assertCount(0, $roleRepository->getByName('Middleware')->getPermissions());
    }

    public function testGetPart(): void
    {
        $this->assertEquals('data', $this->middlewareRoleData->getPart());
    }

    public function testGetPriority(): void
    {
        $this->assertEquals(0, $this->middlewareRoleData->getPriority());
    }
}
