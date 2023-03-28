<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Middleware;

use Codeception\Test\Unit;
use DateTimeImmutable;
use GibsonOS\Core\Dto\Web\Body;
use GibsonOS\Core\Dto\Web\Request;
use GibsonOS\Core\Dto\Web\Response;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\UserError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\Role;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Repository\RoleRepository;
use GibsonOS\Core\Service\SessionService;
use GibsonOS\Core\Service\WebService;
use GibsonOS\Core\Utility\StatusCode;
use GibsonOS\Module\Middleware\Exception\InstanceException;
use GibsonOS\Module\Middleware\Model\Instance;
use GibsonOS\Module\Middleware\Repository\InstanceRepository;
use GibsonOS\Module\Middleware\Service\InstanceService;
use mysqlDatabase;
use mysqlRegistry;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class InstanceServiceTest extends Unit
{
    use ProphecyTrait;

    private InstanceService $instanceService;

    private ObjectProphecy|WebService $webService;

    private ObjectProphecy|InstanceRepository $instanceRepository;

    private ObjectProphecy|RoleRepository $roleRepository;

    private ObjectProphecy|SessionService $sessionService;

    private ObjectProphecy|ModelManager $modelManager;

    private ObjectProphecy|mysqlDatabase $mysqlDatabase;

    protected function _before()
    {
        $this->webService = $this->prophesize(WebService::class);
        $this->instanceRepository = $this->prophesize(InstanceRepository::class);
        $this->roleRepository = $this->prophesize(RoleRepository::class);
        $this->sessionService = $this->prophesize(SessionService::class);
        $this->modelManager = $this->prophesize(ModelManager::class);
        mysqlRegistry::getInstance()->set('database', $this->prophesize(mysqlDatabase::class)->reveal());

        $this->instanceService = new InstanceService(
            $this->instanceRepository->reveal(),
            $this->roleRepository->reveal(),
            $this->sessionService->reveal(),
            $this->modelManager->reveal(),
            $this->webService->reveal()
        );
    }

    public function testTokenLogin(): void
    {
        $user = new User();
        $instance = (new Instance())
            ->setExpireDate(new DateTimeImmutable('+1 second'))
            ->setUser($user)
        ;
        $this->instanceRepository->getByToken('galaxy')
            ->shouldBeCalledOnce()
            ->willReturn($instance)
        ;
        $this->sessionService->login($user)
            ->shouldBeCalledOnce()
        ;

        $this->assertEquals($instance, $this->instanceService->tokenLogin('galaxy'));
    }

    public function testTokenLoginExpired(): void
    {
        $user = new User();
        $instance = (new Instance())
            ->setExpireDate(new DateTimeImmutable('-1 second'))
            ->setUser($user)
        ;
        $this->instanceRepository->getByToken('galaxy')
            ->shouldBeCalledOnce()
            ->willReturn($instance)
        ;

        $this->expectException(UserError::class);

        $this->instanceService->tokenLogin('galaxy');
    }

    public function testTokenLoginUnknownToken(): void
    {
        $this->instanceRepository->getByToken('galaxy')
            ->shouldBeCalledOnce()
            ->willThrow(SelectError::class);

        $this->expectException(UserError::class);

        $this->instanceService->tokenLogin('galaxy');
    }

    public function testAddInstanceUser(): void
    {
        $role = new Role();
        $instance = (new Instance())
            ->setUrl('arthur://dent')
        ;
        $this->roleRepository->getByName('Middleware')
            ->shouldBeCalledOnce()
            ->willReturn($role)
        ;
        $this->modelManager->saveWithoutChildren(Argument::any())
            ->shouldBeCalledTimes(3)
        ;

        $this->instanceService->addInstanceUser($instance);

        $this->assertEquals('arthur://dent', $instance->getUser()->getUser());
        $this->assertEquals($instance->getUser(), $role->getUsers()[0]->getUser());
    }

    public function testSetToken(): void
    {
        $expireDate = new DateTimeImmutable('-1 minute');
        $instance = (new Instance())
            ->setToken('galaxy')
            ->setExpireDate($expireDate)
        ;

        $this->instanceService->setToken($instance);

        $this->assertNotEquals('galaxy', $instance->getToken());
        $this->assertNotEquals($expireDate, $instance->getExpireDate());
    }

    public function testSendRequest(): void
    {
        $instance = (new Instance())
            ->setUrl('arthur://dent/')
            ->setSecret('zaphod')
        ;
        $request = (new Request('arthur://dent/galaxy/ford/prefect'))
            ->setParameters(['marvin' => '42'])
            ->setHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'X-GibsonOs-Secret' => 'zaphod',
            ])
        ;
        $response = new Response(
            $request,
            StatusCode::OK,
            [],
            new Body(),
            '',
        );
        $this->webService->post(Argument::exact($request))
            ->shouldBeCalledOnce()
            ->willReturn($response)
        ;

        $this->assertEquals(
            $response,
            $this->instanceService->sendRequest(
                $instance,
                'galaxy',
                'ford',
                'prefect',
                ['marvin' => '42']
            )
        );
    }

    public function testSendRequestWrongStatusCode(): void
    {
        $instance = (new Instance())
            ->setUrl('arthur://dent/')
            ->setSecret('zaphod')
        ;
        $request = (new Request('arthur://dent/galaxy/ford/prefect'))
            ->setParameters(['marvin' => '42'])
            ->setHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'X-GibsonOs-Secret' => 'zaphod',
            ])
        ;
        $response = new Response(
            $request,
            StatusCode::FORBIDDEN,
            [],
            (new Body())->setContent('trilian', 7),
            '',
        );
        $this->webService->post(Argument::exact($request))
            ->shouldBeCalledOnce()
            ->willReturn($response)
        ;

        $this->expectException(InstanceException::class);

        $this->instanceService->sendRequest(
            $instance,
            'galaxy',
            'ford',
            'prefect',
            ['marvin' => '42']
        );
    }
}
