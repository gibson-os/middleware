<?php
declare(strict_types=1);

namespace GibsonOS\Middleware\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Attribute\GetMappedModel;
use GibsonOS\Core\Controller\AbstractController;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\RequestError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Middleware\Model\Instance;
use GibsonOS\Middleware\Repository\InstanceRepository;
use GibsonOS\Middleware\Service\InstanceService;

class InstanceController extends AbstractController
{
    /**
     * @throws SaveError
     * @throws SelectError
     * @throws RequestError
     * @throws \JsonException
     * @throws \ReflectionException
     */
    #[CheckPermission(Permission::WRITE)]
    public function newToken(
        #[GetMappedModel(['url' => 'url'])] Instance $instance,
        InstanceRepository $instanceRepository,
        InstanceService $instanceService,
        ModelManager $modelManager,
    ): AjaxResponse {
        if ($instance->getId() !== null) {
            $tokenInstance = $instanceRepository->getByToken($this->requestService->getHeader('X-Device-Token'));

            if ($instance->getId() !== $tokenInstance->getId()) {
                return $this->returnFailure('Invalid token');
            }
        }

        if ($instance->getId() === null) {
            $instanceService->addInstanceUser($instance);
        }

        $modelManager->saveWithoutChildren($instanceService->setToken($instance));

        return $this->returnSuccess($instance);
    }
}
