<?php
declare(strict_types=1);

namespace GibsonOS\Test\Functional\Middleware\Controller;

use DateTimeImmutable;
use GibsonOS\Core\Dto\Web\Body;
use GibsonOS\Core\Dto\Web\Request;
use GibsonOS\Core\Dto\Web\Response;
use GibsonOS\Core\Enum\HttpStatusCode;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\Role;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Module\Middleware\Controller\InstanceController;
use GibsonOS\Module\Middleware\Model\Instance;
use GibsonOS\Module\Middleware\Repository\InstanceRepository;
use GibsonOS\Module\Middleware\Service\InstanceService;
use GibsonOS\Test\Functional\Middleware\MiddlewareFunctionalTest;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class InstanceControllerTest extends MiddlewareFunctionalTest
{
    private InstanceController $instanceController;

    private RequestService|ObjectProphecy $requestService;

    protected function _before(): void
    {
        parent::_before();

        $this->requestService = $this->prophesize(RequestService::class);
        $this->serviceManager->setService(RequestService::class, $this->requestService->reveal());

        $this->instanceController = $this->serviceManager->get(InstanceController::class);
    }

    public function testPostNewToken(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $instance = (new Instance($this->modelWrapper))
            ->setUser($this->addUser())
            ->setUrl('http://arthur.dent/')
            ->setToken('ford')
            ->setSecret('prefect')
            ->setExpireDate(new DateTimeImmutable('-1 hour'))
        ;
        $modelManager->saveWithoutChildren($instance);

        $response = new Response(
            new Request('http://arthur.dent/core/middleware/confirm'),
            HttpStatusCode::OK,
            [],
            (new Body())->setContent('{"data": "prefect", "total": 42}', 35),
            ''
        );

        $this->webService->request(Argument::any())
            ->shouldBeCalledOnce()
            ->willReturn($response)
        ;
        $this->requestService->getHeader('X-GibsonOs-Token')
            ->shouldBeCalledOnce()
            ->willReturn('ford')
        ;

        $this->checkSuccessResponse(
            $this->instanceController->postNewToken(
                $instance,
                $this->serviceManager->get(InstanceRepository::class),
                $this->serviceManager->get(InstanceService::class),
                $modelManager
            )
        );

        $this->assertNotEquals('ford', $instance->getToken());
    }

    public function testPostNewTokenAddedSlash(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $instance = (new Instance($this->modelWrapper))
            ->setUser($this->addUser())
            ->setUrl('http://arthur.dent')
            ->setToken('ford')
            ->setSecret('prefect')
            ->setExpireDate(new DateTimeImmutable('-1 hour'))
        ;
        $modelManager->saveWithoutChildren($instance);

        $response = new Response(
            new Request('http://arthur.dent/core/middleware/confirm'),
            HttpStatusCode::OK,
            [],
            (new Body())->setContent('{"data": "prefect", "total": 42}', 35),
            ''
        );

        $this->webService->request(Argument::any())
            ->shouldBeCalledOnce()
            ->willReturn($response)
        ;
        $this->requestService->getHeader('X-GibsonOs-Token')
            ->shouldBeCalledOnce()
            ->willReturn('ford')
        ;

        $this->checkSuccessResponse(
            $this->instanceController->postNewToken(
                $instance,
                $this->serviceManager->get(InstanceRepository::class),
                $this->serviceManager->get(InstanceService::class),
                $modelManager
            )
        );

        $this->assertNotEquals('ford', $instance->getToken());
        $this->assertEquals('http://arthur.dent/', $instance->getUrl());
    }

    public function testPostNewTokenInvalidToken(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $instance = (new Instance($this->modelWrapper))
            ->setUser($this->addUser())
            ->setUrl('http://arthur.dent/')
            ->setToken('ford')
            ->setSecret('prefect')
            ->setExpireDate(new DateTimeImmutable('-1 hour'))
        ;
        $modelManager->saveWithoutChildren($instance);

        $this->requestService->getHeader('X-GibsonOs-Token')
            ->shouldBeCalledOnce()
            ->willReturn('prefect')
        ;

        $this->checkErrorResponse(
            $this->instanceController->postNewToken(
                $instance,
                $this->serviceManager->get(InstanceRepository::class),
                $this->serviceManager->get(InstanceService::class),
                $modelManager
            ),
            'Invalid token',
        );
        $this->assertEquals('ford', $instance->getToken());
    }

    public function testPostNewTokenInvalidInstance(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $instance = (new Instance($this->modelWrapper))
            ->setUser($this->addUser())
            ->setUrl('http://arthur.dent/')
            ->setToken('ford')
            ->setSecret('prefect')
            ->setExpireDate(new DateTimeImmutable('-1 hour'))
        ;
        $modelManager->saveWithoutChildren($instance);
        $modelManager->saveWithoutChildren(
            (new Instance($this->modelWrapper))
                ->setUser($this->addUser())
                ->setUrl('http://ford.prefect/')
                ->setToken('arthur')
                ->setSecret('dent')
                ->setExpireDate(new DateTimeImmutable('+1 hour'))
        );

        $this->requestService->getHeader('X-GibsonOs-Token')
            ->shouldBeCalledOnce()
            ->willReturn('arthur')
        ;

        $this->checkErrorResponse(
            $this->instanceController->postNewToken(
                $instance,
                $this->serviceManager->get(InstanceRepository::class),
                $this->serviceManager->get(InstanceService::class),
                $modelManager
            ),
            'Invalid token'
        );

        $this->assertEquals('ford', $instance->getToken());
    }

    public function testPostNewTokenNewInstance(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $instance = (new Instance($this->modelWrapper))
            ->setUrl('http://arthur.dent/')
            ->setSecret('prefect')
        ;
        $modelManager->saveWithoutChildren((new Role($this->modelWrapper))->setName('Middleware'));

        $response = new Response(
            new Request('http://arthur.dent/core/middleware/confirm'),
            HttpStatusCode::OK,
            [],
            (new Body())->setContent('{"data": "prefect", "total": 42}', 35),
            ''
        );

        $this->webService->request(Argument::any())
            ->shouldBeCalledOnce()
            ->willReturn($response)
        ;

        $this->checkSuccessResponse(
            $this->instanceController->postNewToken(
                $instance,
                $this->serviceManager->get(InstanceRepository::class),
                $this->serviceManager->get(InstanceService::class),
                $modelManager
            )
        );

        $this->assertEquals('http://arthur.dent/', $instance->getUrl());
        $this->assertEquals('prefect', $instance->getSecret());
        $this->assertEquals('http://arthur.dent/', $instance->getUser()->getUser());
        $this->assertNull($instance->getUser()->getPassword());
    }
}
