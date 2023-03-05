<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\RequestError;
use GibsonOS\Core\Service\Attribute\AttributeServiceInterface;
use GibsonOS\Core\Service\Attribute\ParameterAttributeInterface;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Module\Middleware\Attribute\GetInstance;
use GibsonOS\Module\Middleware\Model\Instance;
use GibsonOS\Module\Middleware\Repository\InstanceRepository;

class InstanceAttribute implements ParameterAttributeInterface, AttributeServiceInterface
{
    public function __construct(
        private readonly RequestService $requestService,
        private readonly InstanceRepository $instanceRepository,
    ) {
    }

    /**
     * @throws SelectError
     * @throws RequestError
     */
    public function replace(
        AttributeInterface $attribute,
        array $parameters,
        \ReflectionParameter $reflectionParameter
    ): ?Instance {
        if (!$attribute instanceof GetInstance) {
            return null;
        }

        return $this->instanceRepository->getByToken($this->requestService->getHeader('X-GibsonOs-Token'));
    }
}
