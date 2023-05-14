<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Service;

use DateTimeImmutable;
use Exception;
use GibsonOS\Core\Dto\Web\Request;
use GibsonOS\Core\Dto\Web\Response;
use GibsonOS\Core\Enum\HttpMethod;
use GibsonOS\Core\Enum\HttpStatusCode;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\UserError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\Role\User as RoleUser;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Repository\RoleRepository;
use GibsonOS\Core\Service\SessionService;
use GibsonOS\Core\Service\WebService;
use GibsonOS\Module\Middleware\Exception\InstanceException;
use GibsonOS\Module\Middleware\Model\Instance;
use GibsonOS\Module\Middleware\Repository\InstanceRepository;

class InstanceService
{
    public function __construct(
        private readonly InstanceRepository $instanceRepository,
        private readonly RoleRepository $roleRepository,
        private readonly SessionService $sessionService,
        private readonly ModelManager $modelManager,
        private readonly WebService $webService,
    ) {
    }

    public function tokenLogin(string $token): Instance
    {
        try {
            $instance = $this->instanceRepository->getByToken($token);

            if (new DateTimeImmutable() > $instance->getExpireDate()) {
                throw new UserError('Token expired');
            }

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
        $this->modelManager->saveWithoutChildren($this->roleRepository->getByName('Middleware')->addUsers([$roleUser]));
        $this->modelManager->saveWithoutChildren($roleUser);
        $instance->setUser($user);

        return $instance;
    }

    public function setToken(Instance $instance): Instance
    {
        return $instance
            ->setToken($this->generateToken())
            ->setExpireDate(new DateTimeImmutable('+1 month'))
        ;
    }

    public function sendRequest(
        Instance $instance,
        string $module,
        string $task,
        string $action,
        array $parameters = [],
        HttpMethod $method = HttpMethod::POST,
    ): Response {
        $request = (new Request(sprintf(
            '%s%s/%s/%s',
            $instance->getUrl(),
            $module,
            $task,
            $action
        )))
            ->setParameters($parameters)
            ->setHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'X-GibsonOs-Secret' => $instance->getSecret(),
            ]);

        if ($method === HttpMethod::GET) {
            $response = $this->webService->get($request);
        } else {
            $response = $this->webService->post($request);
        }

        if ($response->getStatusCode() !== HttpStatusCode::OK) {
            throw new InstanceException($response->getBody()->getContent());
        }

        return $response;
    }

    /**
     * @throws Exception
     */
    private function generateToken(): string
    {
        return mb_substr(base64_encode(random_bytes(190)), 0, 256);
    }
}
