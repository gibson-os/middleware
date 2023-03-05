<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Service;

use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\UserError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\Role\User as RoleUser;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Repository\RoleRepository;
use GibsonOS\Core\Service\SessionService;
use GibsonOS\Module\Middleware\Model\Instance;
use GibsonOS\Module\Middleware\Repository\InstanceRepository;

class InstanceService
{
    public function __construct(
        private readonly InstanceRepository $instanceRepository,
        private readonly RoleRepository $roleRepository,
        private readonly SessionService $sessionService,
        private readonly ModelManager $modelManager,
    ) {
    }

    public function tokenLogin(string $token): Instance
    {
        try {
            $instance = $this->instanceRepository->getByToken($token);
            $this->sessionService->login($instance->getUser());

            return $instance;
        } catch (SelectError $exception) {
            throw new UserError($exception->getMessage(), 0, $exception);
        }
    }

    /**
     * @throws SelectError
     * @throws SaveError
     */
    public function addInstanceUser(Instance $instance): Instance
    {
        $user = (new User())->setUser($instance->getUrl());
        $this->modelManager->saveWithoutChildren($user);
        $roleUser = (new RoleUser())->setUser($user);
        $this->modelManager->saveWithoutChildren($roleUser);
        $this->modelManager->saveWithoutChildren($this->roleRepository->getByName('Middleware')->addUsers([$roleUser]));
        $instance->setUser($user);

        return $instance;
    }

    public function setToken(Instance $instance): Instance
    {
        return $instance
            ->setToken($this->generateToken())
            ->setExpireDate(new \DateTimeImmutable('+1 month'))
        ;
    }

    /**
     * @throws \Exception
     */
    private function generateToken(): string
    {
        return mb_substr(base64_encode(random_bytes(190)), 0, 256);
    }
}
