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
use ReflectionParameter;

class InstanceAttribute implements ParameterAttributeInterface, AttributeServiceInterface
{
    public function __construct(
        private readonly RequestService $requestService,
        private readonly InstanceRepository $instanceRepository,
    ) {
    }

    public function replace(
        AttributeInterface $attribute,
        array $parameters,
        ReflectionParameter $reflectionParameter
    ): ?Instance {
        if (!$attribute instanceof GetInstance) {
            return null;
        }

        try {
            return $this->instanceRepository->getByToken($this->requestService->getHeader('X-GibsonOs-Token'));
        } catch (SelectError|RequestError) {
            return null;
        }
    }
}
