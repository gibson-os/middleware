<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Attribute\GetObject;
use GibsonOS\Core\Controller\AbstractController;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\WebException;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Utility\StatusCode;
use GibsonOS\Module\Middleware\Attribute\GetInstance;
use GibsonOS\Module\Middleware\Exception\FcmException;
use GibsonOS\Module\Middleware\Model\Instance;
use GibsonOS\Module\Middleware\Model\Message;
use GibsonOS\Module\Middleware\Repository\MessageRepository;
use JsonException;

class MessageController extends AbstractController
{
    /**
     * @throws SaveError
     * @throws WebException
     * @throws FcmException
     * @throws JsonException
     */
    #[CheckPermission(Permission::WRITE)]
    public function push(
        MessageRepository $messageRepository,
        ModelManager $modelManager,
        #[GetObject] Message $message,
        #[GetInstance] Instance $instance,
    ): AjaxResponse {
        if ($messageRepository->fcmTokenNotFound($message->getFcmToken())) {
            return $this->returnFailure('FCM token not found', StatusCode::NOT_FOUND);
        }

        $message->setInstance($instance);
        $modelManager->saveWithoutChildren($message);

        return $this->returnSuccess();
    }
}
