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
use GibsonOS\Core\Service\WebService;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Core\Utility\StatusCode;
use GibsonOS\Module\Middleware\Attribute\GetInstance;
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
        $response = $webService->get(
            (new Request(sprintf('%sexplorer/html5/toSeeMiddleware', $session->getInstance()->getUrl())))
                ->setHeaders(['Content-Type' => 'application/json'])
        );

        if ($response->getStatusCode() !== StatusCode::OK) {
            return $this->returnFailure($response->getBody()->getContent());
        }

        $body = JsonUtility::decode($response->getBody()->getContent());

        return $this->returnSuccess($body['data'] ?? [], $body['total'] ?? 0);
    }

    /**
     * @throws SaveError
     */
    #[CheckPermission(Permission::WRITE)]
    public function setSession(
        ModelManager $modelManager,
        #[GetModel(['session_id' => 'sessionId'])] Session $session
    ): AjaxResponse {
        $modelManager->saveWithoutChildren($session);

        return $this->returnSuccess();
    }

    #[CheckPermission(Permission::READ)]
    public function getSessionUserIds(
        #[GetModel(['session_id' => 'sessionId'])] Session $session,
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
    public function setUsers(
        ModelManager $modelManager,
        #[GetModel(['session_id' => 'sessionId'])] Session $session,
        #[GetMappedModels(User::class, ['session_id' => 'sessionId', 'user_id' => 'userId'])] array $users,
    ): AjaxResponse {
        $session->setUsers($users);
        $modelManager->save($session);

        return $this->returnSuccess();
    }
}
