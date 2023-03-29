<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Attribute\GetMappedModel;
use GibsonOS\Core\Controller\AbstractController;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\RequestError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Module\Middleware\Exception\InstanceException;
use GibsonOS\Module\Middleware\Model\Instance;
use GibsonOS\Module\Middleware\Repository\InstanceRepository;
use GibsonOS\Module\Middleware\Service\InstanceService;
use JsonException;
use ReflectionException;

class InstanceController extends AbstractController
{
    /**
     * @throws SaveError
     * @throws SelectError
     * @throws RequestError
     * @throws JsonException
     * @throws ReflectionException
     * @throws InstanceException
     */
    #[CheckPermission(Permission::WRITE)]
    public function newToken(
        #[GetMappedModel(['url' => 'url'])] Instance $instance,
        InstanceRepository $instanceRepository,
        InstanceService $instanceService,
        ModelManager $modelManager,
    ): AjaxResponse {
        if ($instance->getId() !== null) {
            try {
                $tokenInstance = $instanceRepository->getByToken($this->requestService->getHeader('X-GibsonOs-Token'));

                if ($instance->getId() !== $tokenInstance->getId()) {
                    return $this->returnFailure('Invalid token');
                }
            } catch (SelectError) {
                return $this->returnFailure('Invalid token');
            }
        }

        $instanceService->setToken($instance);
        $url = $instance->getUrl();

        if (mb_substr($url, -1) !== '/') {
            $instance->setUrl($url . '/');
        }

        $instanceService->sendRequest(
            $instance,
            'core',
            'middleware',
            'confirm',
            ['token' => $instance->getToken()]
        );

        if ($instance->getId() === null) {
            $instanceService->addInstanceUser($instance);
        }

        $modelManager->saveWithoutChildren($instance);

        return $this->returnSuccess();
    }
}
