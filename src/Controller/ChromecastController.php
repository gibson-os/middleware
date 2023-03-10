<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Attribute\GetEnv;
use GibsonOS\Core\Attribute\GetMappedModel;
use GibsonOS\Core\Attribute\GetMappedModels;
use GibsonOS\Core\Attribute\GetModel;
use GibsonOS\Core\Controller\AbstractController;
use GibsonOS\Core\Dto\Web\Request;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\WebException;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Service\Response\Response;
use GibsonOS\Core\Service\Response\ResponseInterface;
use GibsonOS\Core\Service\Response\TwigResponse;
use GibsonOS\Core\Service\WebService;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Core\Utility\StatusCode;
use GibsonOS\Module\Middleware\Attribute\GetInstance;
use GibsonOS\Module\Middleware\Model\Chromecast\Error;
use GibsonOS\Module\Middleware\Model\Chromecast\Session;
use GibsonOS\Module\Middleware\Model\Chromecast\Session\User;
use GibsonOS\Module\Middleware\Model\Instance;

class ChromecastController extends AbstractController
{
    #[CheckPermission(Permission::READ)]
    public function getReceiverAppId(#[GetEnv('CHROMECAST_RECEIVER_APP_ID')] string $receiverAppId): AjaxResponse
    {
        return $this->returnSuccess($receiverAppId);
    }

    /**
     * @throws WebException
     * @throws \JsonException
     */
    #[CheckPermission(Permission::READ)]
    public function toSeeList(
        WebService $webService,
        #[GetModel] Session $session,
    ): AjaxResponse {
        $response = $webService->post(
            (new Request(sprintf('%sexplorer/middleware/toSeeList', $session->getInstance()->getUrl())))
                ->setParameters(['sessionId' => $session->getId()])
                ->setHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        );

        $body = $response->getBody()->getContent();

        if ($response->getStatusCode() !== StatusCode::OK) {
            return $this->returnFailure($body);
        }

        $body = JsonUtility::decode($body);

        return $this->returnSuccess($body['data'] ?? [], $body['total'] ?? 0);
    }

    /**
     * @throws SaveError
     */
    #[CheckPermission(Permission::WRITE)]
    public function setSession(
        ModelManager $modelManager,
        #[GetMappedModel] Session $session,
        #[GetInstance] Instance $instance,
    ): AjaxResponse {
        $modelManager->saveWithoutChildren($session->setInstance($instance));

        return $this->returnSuccess();
    }

    #[CheckPermission(Permission::READ)]
    public function getSessionUserIds(
        #[GetModel] Session $session,
        #[GetInstance] Instance $instance,
    ): AjaxResponse {
        if ($instance->getId() !== $session->getInstanceId()) {
            return $this->returnFailure('Session not found!', StatusCode::NOT_FOUND);
        }

        return $this->returnSuccess(array_map(
            static fn (User $user): int => $user->getUserId(),
            $session->getUsers(),
        ));
    }

    /**
     * @throws SaveError
     */
    #[CheckPermission(Permission::WRITE)]
    public function addUser(
        ModelManager $modelManager,
        #[GetMappedModel(['session_id' => 'sessionId', 'user_id' => 'userId'])] User $user,
    ): AjaxResponse {
        $modelManager->saveWithoutChildren($user);

        return $this->returnSuccess();
    }

    /**
     * @throws SaveError
     * @throws \JsonException
     * @throws \ReflectionException
     */
    #[CheckPermission(Permission::WRITE)]
    public function savePosition(
        WebService $webService,
        ModelManager $modelManager,
        #[GetModel] Session $session,
        #[GetMappedModels(User::class, ['session_id' => 'sessionId', 'user_id' => 'userId'])] array $users,
        string $token,
        int $position,
    ): AjaxResponse {
        $session->setUsers($users);
        $modelManager->save($session);

        $response = $webService->post(
            (new Request(sprintf('%sexplorer/middleware/savePosition', $session->getInstance()->getUrl())))
                ->setParameters([
                    'sessionId' => $session->getId(),
                    'token' => $token,
                    'position' => (string) $position,
                ])
                ->setHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        );

        if ($response->getStatusCode() !== StatusCode::OK) {
            return $this->returnFailure($response->getBody()->getContent());
        }

        return $this->returnSuccess();
    }

    #[CheckPermission(Permission::READ)]
    public function get(
        WebService $webService,
        #[GetModel] Session $session,
        string $token,
    ): AjaxResponse {
        $response = $webService->post(
            (new Request(sprintf('%sexplorer/middleware/get', $session->getInstance()->getUrl())))
                ->setParameters([
                    'sessionId' => $session->getId(),
                    'token' => $token,
                ])
                ->setHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        );

        $body = $response->getBody()->getContent();

        if ($response->getStatusCode() !== StatusCode::OK) {
            return $this->returnFailure($body);
        }

        return $this->returnSuccess(JsonUtility::decode($body)['data']);
    }

    #[CheckPermission(Permission::READ)]
    public function show(): TwigResponse
    {
        return $this->renderTemplate('@middleware/chromecast.html.twig');
    }

    #[CheckPermission(Permission::READ)]
    public function image(
        WebService $webService,
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

        $response = $webService->post(
            (new Request(sprintf('%sexplorer/middleware/image', $session->getInstance()->getUrl())))
                ->setParameters($parameters)
        );

        $body = $response->getBody()->getContent();

        if ($response->getStatusCode() !== StatusCode::OK) {
            return $this->returnFailure($body);
        }

        return new Response(
            $body,
            StatusCode::OK,
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

    #[CheckPermission(Permission::WRITE)]
    public function error(
        #[GetMappedModel] Error $error,
        ModelManager $modelManager,
    ): AjaxResponse {
        $modelManager->saveWithoutChildren($error);

        return $this->returnSuccess();
    }
}
