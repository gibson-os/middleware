<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Attribute\GetObject;
use GibsonOS\Core\Controller\AbstractController;
use GibsonOS\Core\Enum\HttpStatusCode;
use GibsonOS\Core\Enum\Permission;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Module\Middleware\Attribute\GetInstance;
use GibsonOS\Module\Middleware\Model\Instance;
use GibsonOS\Module\Middleware\Model\Message;
use GibsonOS\Module\Middleware\Repository\MessageRepository;

class MessageController extends AbstractController
{
    /**
     * @throws SaveError
     */
    #[CheckPermission([Permission::WRITE])]
    public function postPush(
        MessageRepository $messageRepository,
        ModelManager $modelManager,
        #[GetObject] Message $message,
        #[GetInstance] Instance $instance,
    ): AjaxResponse {
        if ($messageRepository->fcmTokenNotFound($message->getFcmToken())) {
            return $this->returnFailure('FCM token not found', HttpStatusCode::NOT_FOUND);
        }

        $message->setInstance($instance);
        $modelManager->saveWithoutChildren($message);

        return $this->returnSuccess();
    }
}
