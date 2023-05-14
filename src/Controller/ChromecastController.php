<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Controller;

use DateTimeImmutable;
use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Attribute\GetEnv;
use GibsonOS\Core\Attribute\GetMappedModel;
use GibsonOS\Core\Attribute\GetMappedModels;
use GibsonOS\Core\Attribute\GetModel;
use GibsonOS\Core\Controller\AbstractController;
use GibsonOS\Core\Enum\HttpMethod;
use GibsonOS\Core\Enum\HttpStatusCode;
use GibsonOS\Core\Enum\Permission;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\WebException;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Service\Response\Response;
use GibsonOS\Core\Service\Response\ResponseInterface;
use GibsonOS\Core\Service\Response\TwigResponse;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Module\Middleware\Attribute\GetInstance;
use GibsonOS\Module\Middleware\Exception\InstanceException;
use GibsonOS\Module\Middleware\Model\Chromecast\Error;
use GibsonOS\Module\Middleware\Model\Chromecast\Session;
use GibsonOS\Module\Middleware\Model\Chromecast\Session\User;
use GibsonOS\Module\Middleware\Model\Instance;
use GibsonOS\Module\Middleware\Repository\Chromecast\Session\UserRepository;
use GibsonOS\Module\Middleware\Service\InstanceService;
use JsonException;
use ReflectionException;

class ChromecastController extends AbstractController
{
    #[CheckPermission([Permission::READ])]
    public function getReceiverAppId(#[GetEnv('CHROMECAST_RECEIVER_APP_ID')] string $receiverAppId): AjaxResponse
    {
        return $this->returnSuccess($receiverAppId);
    }

    /**
     * @throws WebException
     * @throws JsonException
     * @throws InstanceException
     * @throws SaveError
     */
    #[CheckPermission([Permission::READ])]
    public function getToSeeList(
        ModelManager $modelManager,
        InstanceService $instanceService,
        #[GetModel] Session $session,
    ): AjaxResponse {
        $response = $instanceService->sendRequest(
            $session->getInstance(),
            'explorer',
            'middleware',
            'toSeeList',
            ['sessionId' => $session->getId()],
            HttpMethod::GET,
        );
        $body = JsonUtility::decode($response->getBody()->getContent());
        $modelManager->saveWithoutChildren($session->setLastUpdate(new DateTimeImmutable()));

        return $this->returnSuccess($body['data'] ?? [], $body['total'] ?? 0);
    }

    /**
     * @throws SaveError
     */
    #[CheckPermission([Permission::WRITE])]
    public function postSetSession(
        ModelManager $modelManager,
        #[GetMappedModel] Session $session,
        #[GetInstance] Instance $instance,
    ): AjaxResponse {
        $modelManager->saveWithoutChildren($session->setInstance($instance));

        return $this->returnSuccess();
    }

    #[CheckPermission([Permission::READ])]
    public function getSessionUserIds(
        #[GetModel] Session $session,
        #[GetInstance] Instance $instance,
    ): AjaxResponse {
        if ($instance->getId() !== $session->getInstanceId()) {
            return $this->returnFailure('Session not found!', HttpStatusCode::NOT_FOUND);
        }

        return $this->returnSuccess(array_map(
            static fn (User $user): int => $user->getUserId(),
            $session->getUsers(),
        ));
    }

    /**
     * @throws SaveError
     */
    #[CheckPermission([Permission::WRITE])]
    public function postAddUser(
        ModelManager $modelManager,
        #[GetMappedModel(['session_id' => 'sessionId', 'user_id' => 'userId'])] User $user,
    ): AjaxResponse {
        $modelManager->saveWithoutChildren($user);
        $modelManager->saveWithoutChildren($user->getSession()->setLastUpdate(new DateTimeImmutable()));

        return $this->returnSuccess();
    }

    /**
     * @throws SaveError
     * @throws JsonException
     * @throws ReflectionException
     * @throws InstanceException
     * @throws SelectError
     */
    #[CheckPermission([Permission::WRITE])]
    public function postSavePosition(
        InstanceService $instanceService,
        ModelManager $modelManager,
        UserRepository $userRepository,
        #[GetModel] Session $session,
        #[GetMappedModels(User::class, ['session_id' => 'sessionId', 'user_id' => 'userId'])] array $users,
        string $token,
        int $position,
    ): AjaxResponse {
        if (count($users) === 0) {
            $users = [$userRepository->getFirst($session)];
        }

        $modelManager->save($session->setUsers($users));
        $instanceService->sendRequest(
            $session->getInstance(),
            'explorer',
            'middleware',
            'savePosition',
            [
                'sessionId' => $session->getId(),
                'token' => $token,
                'position' => (string) $position,
            ]
        );
        $modelManager->saveWithoutChildren($session->setLastUpdate(new DateTimeImmutable()));

        return $this->returnSuccess();
    }

    /**
     * @throws InstanceException
     * @throws WebException
     * @throws JsonException
     * @throws SaveError
     */
    #[CheckPermission([Permission::READ])]
    public function get(
        ModelManager $modelManager,
        InstanceService $instanceService,
        #[GetModel] Session $session,
        string $token,
    ): AjaxResponse {
        $response = $instanceService->sendRequest(
            $session->getInstance(),
            'explorer',
            'middleware',
            '',
            [
                'sessionId' => $session->getId(),
                'token' => $token,
            ],
            HttpMethod::GET,
        );
        $modelManager->saveWithoutChildren($session->setLastUpdate(new DateTimeImmutable()));

        return $this->returnSuccess(JsonUtility::decode($response->getBody()->getContent())['data']);
    }

    #[CheckPermission([Permission::READ])]
    public function getShow(): TwigResponse
    {
        return $this->renderTemplate('@middleware/chromecast.html.twig');
    }

    /**
     * @throws WebException
     * @throws InstanceException
     * @throws SaveError
     */
    #[CheckPermission([Permission::READ])]
    public function getImage(
        ModelManager $modelManager,
        InstanceService $instanceService,
        #[GetModel] Session $session,
        string $token,
        int $width = null,
        int $height = null,
    ): ResponseInterface {
        $parameters = [
            'sessionId' => $session->getId(),
            'token' => $token,
        ];

        if ($width !== null) {
            $parameters['width'] = (string) $width;
        }

        if ($height !== null) {
            $parameters['height'] = (string) $height;
        }

        $response = $instanceService->sendRequest(
            $session->getInstance(),
            'explorer',
            'middleware',
            'image',
            $parameters,
            HttpMethod::GET,
        );
        $body = $response->getBody()->getContent();
        $modelManager->saveWithoutChildren($session->setLastUpdate(new DateTimeImmutable()));

        return new Response(
            $body,
            HttpStatusCode::OK,
            [
                'Pragma' => 'public',
                'Expires' => 0,
                'Accept-Ranges' => 'bytes',
                'Cache-Control' => ['must-revalidate, post-check=0, pre-check=0', 'private'],
                'Content-Type' => 'image/jpg',
                'Content-Length' => strlen($body),
                'Content-Transfer-Encoding' => 'binary',
                'Content-Disposition' => 'inline; filename*=UTF-8\'\'image.jpg filename="image.jpg"',
            ]
        );
    }

    /**
     * @throws SaveError
     */
    #[CheckPermission([Permission::WRITE])]
    public function postError(
        ModelManager $modelManager,
        #[GetMappedModel] Error $error,
    ): AjaxResponse {
        $error->setInstanceId($error->getSession()->getInstanceId());
        $modelManager->saveWithoutChildren($error);

        return $this->returnSuccess();
    }
}
