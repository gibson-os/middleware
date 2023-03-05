<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Controller;

use GibsonOS\Core\Attribute\GetMappedModel;
use GibsonOS\Core\Controller\AbstractController;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\WebException;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Module\Middleware\Attribute\GetInstance;
use GibsonOS\Module\Middleware\Exception\FcmException;
use GibsonOS\Module\Middleware\Model\Instance;
use GibsonOS\Module\Middleware\Model\Message;
use GibsonOS\Module\Middleware\Service\FcmService;

class MessageController extends AbstractController
{
    /**
     * @throws SaveError
     * @throws WebException
     * @throws FcmException
     * @throws \JsonException
     */
    public function push(
        FcmService $fcmService,
        ModelManager $modelManager,
        #[GetMappedModel] Message $message,
        #[GetInstance] Instance $instance,
    ): AjaxResponse {
        $message->setInstance($instance);
        $fcmService->pushMessage($message);
        $modelManager->saveWithoutChildren($message);

        return $this->returnSuccess();
    }
}
