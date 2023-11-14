<?php
declare(strict_types=1);

namespace GibsonOS\Test\Functional\Middleware\Controller;

use DateTimeImmutable;
use GibsonOS\Core\Dto\Web\Body;
use GibsonOS\Core\Dto\Web\Request;
use GibsonOS\Core\Dto\Web\Response;
use GibsonOS\Core\Enum\HttpMethod;
use GibsonOS\Core\Enum\HttpStatusCode;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Service\TwigService;
use GibsonOS\Module\Middleware\Controller\ChromecastController;
use GibsonOS\Module\Middleware\Model\Chromecast\Error;
use GibsonOS\Module\Middleware\Model\Chromecast\Session;
use GibsonOS\Module\Middleware\Model\Chromecast\Session\User;
use GibsonOS\Module\Middleware\Model\Instance;
use GibsonOS\Module\Middleware\Repository\Chromecast\Session\UserRepository;
use GibsonOS\Module\Middleware\Service\InstanceService;
use GibsonOS\Test\Functional\Middleware\MiddlewareFunctionalTest;
use Twig\Loader\FilesystemLoader;

class ChromecastControllerTest extends MiddlewareFunctionalTest
{
    private ChromecastController $chromecastController;

    protected function _before(): void
    {
        parent::_before();

        $this->chromecastController = $this->serviceManager->get(ChromecastController::class);
    }

    public function testGetReceiverAppId(): void
    {
        $this->checkSuccessResponse(
            $this->chromecastController->getReceiverAppId('galaxy'),
            'galaxy'
        );
    }

    public function testGetToSeeList(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $user = $this->addUser();
        $instance = (new Instance($this->modelWrapper))
            ->setUser($user)
            ->setUrl('http://arthur.dent/')
            ->setToken('ford')
            ->setSecret('prefect')
            ->setExpireDate(new DateTimeImmutable('+1 hour'))
        ;
        $modelManager->saveWithoutChildren($instance);
        $session = (new Session($this->modelWrapper))
            ->setId('marvin')
            ->setInstance($instance)
        ;
        $modelManager->save($session);

        $request = (new Request('http://arthur.dent/explorer/middleware/toSeeList'))
            ->setMethod(HttpMethod::GET)
            ->setParameters(['sessionId' => 'marvin'])
            ->setHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'X-GibsonOs-Secret' => $instance->getSecret(),
            ])
        ;
        $response = new Response(
            $request,
            HttpStatusCode::OK,
            [],
            (new Body())->setContent('{"data": "prefect", "total": 42}', 35),
            ''
        );
        $this->webService->request($request)
            ->shouldBeCalledOnce()
            ->willReturn($response)
        ;

        $this->checkSuccessResponse(
            $this->chromecastController->getToSeeList(
                $modelManager,
                $this->serviceManager->get(InstanceService::class),
                $session,
            ),
            'prefect',
            42
        );
    }

    public function testPostSession(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $user = $this->addUser();
        $instance = (new Instance($this->modelWrapper))
            ->setUser($user)
            ->setUrl('http://arthur.dent/')
            ->setToken('ford')
            ->setSecret('prefect')
            ->setExpireDate(new DateTimeImmutable('+1 hour'))
        ;
        $modelManager->saveWithoutChildren($instance);
        $session = (new Session($this->modelWrapper))
            ->setId('marvin')
        ;

        $this->checkSuccessResponse(
            $this->chromecastController->postSession(
                $modelManager,
                $session,
                $instance,
            )
        );

        $this->assertEquals($session->getInstance(), $instance);
    }

    public function testGetSessionUserIds(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $user = $this->addUser();
        $instance = (new Instance($this->modelWrapper))
            ->setUser($user)
            ->setUrl('http://arthur.dent/')
            ->setToken('ford')
            ->setSecret('prefect')
            ->setExpireDate(new DateTimeImmutable('+1 hour'))
        ;
        $modelManager->saveWithoutChildren($instance);
        $session = (new Session($this->modelWrapper))
            ->setId('marvin')
            ->setInstance($instance)
            ->setUsers([(new User($this->modelWrapper))->setUserId(42)->setSenderId('marvin')])
        ;
        $modelManager->save($session);

        $this->checkSuccessResponse(
            $this->chromecastController->getSessionUserIds($session, $instance),
            [42],
        );
    }

    public function testGetSessionUserIdsWrongInstance(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $user = $this->addUser();
        $instance = (new Instance($this->modelWrapper))
            ->setUser($user)
            ->setUrl('http://arthur.dent/')
            ->setToken('ford')
            ->setSecret('prefect')
            ->setExpireDate(new DateTimeImmutable('+1 hour'))
        ;
        $modelManager->saveWithoutChildren($instance);
        $instance2 = (new Instance($this->modelWrapper))
            ->setUser($this->addUser('zaphod'))
            ->setUrl('http://arthur.dent/')
            ->setToken('ford')
            ->setSecret('prefect')
            ->setExpireDate(new DateTimeImmutable('+1 hour'))
        ;
        $modelManager->saveWithoutChildren($instance2);
        $session = (new Session($this->modelWrapper))
            ->setId('marvin')
            ->setInstance($instance)
            ->setUsers([(new User($this->modelWrapper))->setUserId(42)->setSenderId('marvin')])
        ;
        $modelManager->save($session);

        $this->checkErrorResponse(
            $this->chromecastController->getSessionUserIds($session, $instance2),
            'Session not found!',
        );
    }

    public function testAddUser(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $instance = (new Instance($this->modelWrapper))
            ->setUser($this->addUser())
            ->setUrl('http://arthur.dent/')
            ->setToken('ford')
            ->setSecret('prefect')
            ->setExpireDate(new DateTimeImmutable('+1 hour'))
        ;
        $modelManager->saveWithoutChildren($instance);
        $session = (new Session($this->modelWrapper))
            ->setId('marvin')
            ->setInstance($instance)
            ->setUsers([(new User($this->modelWrapper))->setUserId(42)->setSenderId('marvin')])
        ;
        $modelManager->save($session);
        $user = (new User($this->modelWrapper))
            ->setSession($session)
            ->setUserId(42)
            ->setSenderId('marvin')
        ;
        $oldLastUpdate = $session->getLastUpdate();

        $this->checkSuccessResponse($this->chromecastController->postUser($modelManager, $user));

        $this->assertNotSame($oldLastUpdate, $session->getLastUpdate());
        $savedUser = $this->serviceManager->get(UserRepository::class)->getFirst($session);
        $this->assertEquals($user->getSessionId(), $savedUser->getSessionId());
        $this->assertEquals($user->getUserId(), $savedUser->getUserId());
        $this->assertEquals($user->getSenderId(), $savedUser->getSenderId());
    }

    public function testPostPosition(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $instance = (new Instance($this->modelWrapper))
            ->setUser($this->addUser())
            ->setUrl('http://arthur.dent/')
            ->setToken('ford')
            ->setSecret('prefect')
            ->setExpireDate(new DateTimeImmutable('+1 hour'))
        ;
        $modelManager->saveWithoutChildren($instance);
        $user = (new User($this->modelWrapper))->setUserId(42)->setSenderId('marvin');
        $session = (new Session($this->modelWrapper))
            ->setId('marvin')
            ->setInstance($instance)
        ;
        $modelManager->saveWithoutChildren($session);

        $request = (new Request('http://arthur.dent/explorer/middleware/position'))
            ->setMethod(HttpMethod::POST)
            ->setParameters([
                'sessionId' => 'marvin',
                'token' => 'galaxy',
                'position' => '42',
            ])
            ->setHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'X-GibsonOs-Secret' => $instance->getSecret(),
            ])
        ;
        $response = new Response(
            $request,
            HttpStatusCode::OK,
            [],
            new Body(),
            ''
        );
        $this->webService->request($request)
            ->shouldBeCalledOnce()
            ->willReturn($response)
        ;
        $oldLastUpdate = $session->getLastUpdate();

        $this->checkSuccessResponse(
            $this->chromecastController->postPosition(
                $this->serviceManager->get(InstanceService::class),
                $modelManager,
                $this->serviceManager->get(UserRepository::class),
                $session,
                [$user],
                'galaxy',
                42,
            )
        );

        $this->assertNotSame($oldLastUpdate, $session->getLastUpdate());
        $this->assertSame([$user], $session->getUsers());
    }

    public function testPostPositionNoUsers(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $instance = (new Instance($this->modelWrapper))
            ->setUser($this->addUser())
            ->setUrl('http://arthur.dent/')
            ->setToken('ford')
            ->setSecret('prefect')
            ->setExpireDate(new DateTimeImmutable('+1 hour'))
        ;
        $modelManager->saveWithoutChildren($instance);
        $user = (new User($this->modelWrapper))->setUserId(42)->setSenderId('marvin');
        $session = (new Session($this->modelWrapper))
            ->setId('marvin')
            ->setInstance($instance)
            ->setUsers([$user])
        ;
        $modelManager->save($session);

        $request = (new Request('http://arthur.dent/explorer/middleware/position'))
            ->setMethod(HttpMethod::POST)
            ->setParameters([
                'sessionId' => 'marvin',
                'token' => 'galaxy',
                'position' => '42',
            ])
            ->setHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'X-GibsonOs-Secret' => $instance->getSecret(),
            ])
        ;
        $response = new Response(
            $request,
            HttpStatusCode::OK,
            [],
            new Body(),
            ''
        );
        $this->webService->request($request)
            ->shouldBeCalledOnce()
            ->willReturn($response)
        ;
        $oldLastUpdate = $session->getLastUpdate();

        $this->checkSuccessResponse(
            $this->chromecastController->postPosition(
                $this->serviceManager->get(InstanceService::class),
                $modelManager,
                $this->serviceManager->get(UserRepository::class),
                $session,
                [],
                'galaxy',
                42,
            ),
        );

        $this->assertNotSame($oldLastUpdate, $session->getLastUpdate());
        $this->assertEquals([$user], $session->getUsers());
    }

    public function testGet(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $instance = (new Instance($this->modelWrapper))
            ->setUser($this->addUser())
            ->setUrl('http://arthur.dent/')
            ->setToken('ford')
            ->setSecret('prefect')
            ->setExpireDate(new DateTimeImmutable('+1 hour'))
        ;
        $modelManager->saveWithoutChildren($instance);
        $session = (new Session($this->modelWrapper))
            ->setId('marvin')
            ->setInstance($instance)
        ;
        $modelManager->saveWithoutChildren($session);

        $request = (new Request('http://arthur.dent/explorer/middleware/'))
            ->setMethod(HttpMethod::GET)
            ->setParameters([
                'sessionId' => 'marvin',
                'token' => 'galaxy',
            ])
            ->setHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'X-GibsonOs-Secret' => $instance->getSecret(),
            ])
        ;
        $response = new Response(
            $request,
            HttpStatusCode::OK,
            [],
            (new Body())->setContent('{"data": "prefect"}', 22),
            ''
        );
        $this->webService->request($request)
            ->shouldBeCalledOnce()
            ->willReturn($response)
        ;
        $oldLastUpdate = $session->getLastUpdate();

        $this->checkSuccessResponse(
            $this->chromecastController->get(
                $modelManager,
                $this->serviceManager->get(InstanceService::class),
                $session,
                'galaxy',
            ),
            'prefect'
        );

        $this->assertNotSame($oldLastUpdate, $session->getLastUpdate());
    }

    public function testGetShow(): void
    {
        $loader = new FilesystemLoader();
        $templatePath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'template';
        $loader->addPath($templatePath, 'middleware');
        $this->serviceManager->get(TwigService::class)->getTwig()->setLoader($loader);

        $response = $this->chromecastController->getShow();

        $body = $response->getBody();
        $this->assertEquals(
            file_get_contents($templatePath . DIRECTORY_SEPARATOR . 'chromecast.html.twig'),
            $body,
        );
        $this->assertStringContainsString('id="media"', $body);
        $this->assertStringContainsString('id="title"', $body);
        $this->assertStringContainsString('id="image"', $body);
        $this->assertStringContainsString('id="nextFiles"', $body);
        $this->assertStringContainsString('<footer', $body);
        $this->assertStringContainsString('id="messageContainer"', $body);
        $this->assertStringContainsString('id="message"', $body);
        $this->assertStringContainsString('id="messageImage"', $body);
        $this->assertStringContainsString('id="time"', $body);
        $this->assertStringContainsString('id="timeline"', $body);
        $this->assertStringContainsString('class="bar"', $body);
        $this->assertStringContainsString('class="duration"', $body);
        $this->assertStringContainsString('div class="position"', $body);
        $this->assertStringContainsString('class="currentPosition"', $body);
    }

    public function testGetImage(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $instance = (new Instance($this->modelWrapper))
            ->setUser($this->addUser())
            ->setUrl('http://arthur.dent/')
            ->setToken('ford')
            ->setSecret('prefect')
            ->setExpireDate(new DateTimeImmutable('+1 hour'))
        ;
        $modelManager->saveWithoutChildren($instance);
        $session = (new Session($this->modelWrapper))
            ->setId('marvin')
            ->setInstance($instance)
        ;
        $modelManager->saveWithoutChildren($session);

        $request = (new Request('http://arthur.dent/explorer/middleware/image'))
            ->setMethod(HttpMethod::GET)
            ->setParameters([
                'sessionId' => 'marvin',
                'token' => 'galaxy',
                'width' => 42,
                'height' => 42,
            ])
            ->setHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'X-GibsonOs-Secret' => $instance->getSecret(),
            ])
        ;
        $response = new Response(
            $request,
            HttpStatusCode::OK,
            [],
            (new Body())->setContent('prefect', 7),
            ''
        );
        $this->webService->request($request)
            ->shouldBeCalledOnce()
            ->willReturn($response)
        ;
        $oldLastUpdate = $session->getLastUpdate();

        $imageResponse = $this->chromecastController->getImage(
            $modelManager,
            $this->serviceManager->get(InstanceService::class),
            $session,
            'galaxy',
            42,
            42,
        );

        $this->assertEquals('prefect', $imageResponse->getBody());
        $this->assertEquals(
            [
                'Pragma' => 'public',
                'Expires' => 0,
                'Accept-Ranges' => 'bytes',
                'Cache-Control' => ['must-revalidate, post-check=0, pre-check=0', 'private'],
                'Content-Type' => 'image/jpg',
                'Content-Length' => 7,
                'Content-Transfer-Encoding' => 'binary',
                'Content-Disposition' => 'inline; filename*=UTF-8\'\'image.jpg filename="image.jpg"',
            ],
            $imageResponse->getHeaders()
        );
        $this->assertNotSame($oldLastUpdate, $session->getLastUpdate());
    }

    public function testGetImageWithoutWidth(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $instance = (new Instance($this->modelWrapper))
            ->setUser($this->addUser())
            ->setUrl('http://arthur.dent/')
            ->setToken('ford')
            ->setSecret('prefect')
            ->setExpireDate(new DateTimeImmutable('+1 hour'))
        ;
        $modelManager->saveWithoutChildren($instance);
        $session = (new Session($this->modelWrapper))
            ->setId('marvin')
            ->setInstance($instance)
        ;
        $modelManager->saveWithoutChildren($session);

        $request = (new Request('http://arthur.dent/explorer/middleware/image'))
            ->setMethod(HttpMethod::GET)
            ->setParameters([
                'sessionId' => 'marvin',
                'token' => 'galaxy',
                'height' => 42,
            ])
            ->setHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'X-GibsonOs-Secret' => $instance->getSecret(),
            ])
        ;
        $response = new Response(
            $request,
            HttpStatusCode::OK,
            [],
            (new Body())->setContent('prefect', 7),
            ''
        );
        $this->webService->request($request)
            ->shouldBeCalledOnce()
            ->willReturn($response)
        ;
        $oldLastUpdate = $session->getLastUpdate();

        $imageResponse = $this->chromecastController->getImage(
            $modelManager,
            $this->serviceManager->get(InstanceService::class),
            $session,
            'galaxy',
            height: 42,
        );

        $this->assertEquals('prefect', $imageResponse->getBody());
        $this->assertEquals(
            [
                'Pragma' => 'public',
                'Expires' => 0,
                'Accept-Ranges' => 'bytes',
                'Cache-Control' => ['must-revalidate, post-check=0, pre-check=0', 'private'],
                'Content-Type' => 'image/jpg',
                'Content-Length' => 7,
                'Content-Transfer-Encoding' => 'binary',
                'Content-Disposition' => 'inline; filename*=UTF-8\'\'image.jpg filename="image.jpg"',
            ],
            $imageResponse->getHeaders()
        );
        $this->assertNotSame($oldLastUpdate, $session->getLastUpdate());
    }

    public function testGetImageWithoutHeight(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $instance = (new Instance($this->modelWrapper))
            ->setUser($this->addUser())
            ->setUrl('http://arthur.dent/')
            ->setToken('ford')
            ->setSecret('prefect')
            ->setExpireDate(new DateTimeImmutable('+1 hour'))
        ;
        $modelManager->saveWithoutChildren($instance);
        $session = (new Session($this->modelWrapper))
            ->setId('marvin')
            ->setInstance($instance)
        ;
        $modelManager->saveWithoutChildren($session);

        $request = (new Request('http://arthur.dent/explorer/middleware/image'))
            ->setMethod(HttpMethod::GET)
            ->setParameters([
                'sessionId' => 'marvin',
                'token' => 'galaxy',
                'width' => 42,
            ])
            ->setHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'X-GibsonOs-Secret' => $instance->getSecret(),
            ])
        ;
        $response = new Response(
            $request,
            HttpStatusCode::OK,
            [],
            (new Body())->setContent('prefect', 7),
            ''
        );
        $this->webService->request($request)
            ->shouldBeCalledOnce()
            ->willReturn($response)
        ;
        $oldLastUpdate = $session->getLastUpdate();

        $imageResponse = $this->chromecastController->getImage(
            $modelManager,
            $this->serviceManager->get(InstanceService::class),
            $session,
            'galaxy',
            42,
        );

        $this->assertEquals('prefect', $imageResponse->getBody());
        $this->assertEquals(
            [
                'Pragma' => 'public',
                'Expires' => 0,
                'Accept-Ranges' => 'bytes',
                'Cache-Control' => ['must-revalidate, post-check=0, pre-check=0', 'private'],
                'Content-Type' => 'image/jpg',
                'Content-Length' => 7,
                'Content-Transfer-Encoding' => 'binary',
                'Content-Disposition' => 'inline; filename*=UTF-8\'\'image.jpg filename="image.jpg"',
            ],
            $imageResponse->getHeaders()
        );
        $this->assertNotSame($oldLastUpdate, $session->getLastUpdate());
    }

    public function testGetImageWithoutWidthAndHeight(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $instance = (new Instance($this->modelWrapper))
            ->setUser($this->addUser())
            ->setUrl('http://arthur.dent/')
            ->setToken('ford')
            ->setSecret('prefect')
            ->setExpireDate(new DateTimeImmutable('+1 hour'))
        ;
        $modelManager->saveWithoutChildren($instance);
        $session = (new Session($this->modelWrapper))
            ->setId('marvin')
            ->setInstance($instance)
        ;
        $modelManager->saveWithoutChildren($session);

        $request = (new Request('http://arthur.dent/explorer/middleware/image'))
            ->setMethod(HttpMethod::GET)
            ->setParameters([
                'sessionId' => 'marvin',
                'token' => 'galaxy',
            ])
            ->setHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'X-GibsonOs-Secret' => $instance->getSecret(),
            ])
        ;
        $response = new Response(
            $request,
            HttpStatusCode::OK,
            [],
            (new Body())->setContent('prefect', 7),
            ''
        );
        $this->webService->request($request)
            ->shouldBeCalledOnce()
            ->willReturn($response)
        ;
        $oldLastUpdate = $session->getLastUpdate();

        $imageResponse = $this->chromecastController->getImage(
            $modelManager,
            $this->serviceManager->get(InstanceService::class),
            $session,
            'galaxy',
        );

        $this->assertEquals('prefect', $imageResponse->getBody());
        $this->assertEquals(
            [
                'Pragma' => 'public',
                'Expires' => 0,
                'Accept-Ranges' => 'bytes',
                'Cache-Control' => ['must-revalidate, post-check=0, pre-check=0', 'private'],
                'Content-Type' => 'image/jpg',
                'Content-Length' => 7,
                'Content-Transfer-Encoding' => 'binary',
                'Content-Disposition' => 'inline; filename*=UTF-8\'\'image.jpg filename="image.jpg"',
            ],
            $imageResponse->getHeaders()
        );
        $this->assertNotSame($oldLastUpdate, $session->getLastUpdate());
    }

    public function testPostError(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $instance = (new Instance($this->modelWrapper))
            ->setUser($this->addUser())
            ->setUrl('http://arthur.dent/')
            ->setToken('ford')
            ->setSecret('prefect')
            ->setExpireDate(new DateTimeImmutable('+1 hour'))
        ;
        $modelManager->saveWithoutChildren($instance);
        $session = (new Session($this->modelWrapper))
            ->setId('marvin')
            ->setInstance($instance)
        ;
        $modelManager->saveWithoutChildren($session);

        $error = (new Error($this->modelWrapper))
            ->setSession($session)
            ->setMessage('no hope')
        ;

        $this->checkSuccessResponse($this->chromecastController->postError($modelManager, $error));

        $this->assertEquals($instance->getId(), $error->getInstanceId());
    }
}
