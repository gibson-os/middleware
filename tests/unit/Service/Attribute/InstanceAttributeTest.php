<?php
declare(strict_types=1);

namespace unit\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\RequestError;
use GibsonOS\Module\Middleware\Attribute\GetInstance;
use GibsonOS\Module\Middleware\Model\Instance;
use GibsonOS\Module\Middleware\Repository\InstanceRepository;
use GibsonOS\Module\Middleware\Service\Attribute\InstanceAttribute;
use GibsonOS\UnitTest\AbstractTest;
use Prophecy\Prophecy\ObjectProphecy;

class InstanceAttributeTest extends AbstractTest
{
    private InstanceAttribute $instanceAttribute;

    private InstanceRepository|ObjectProphecy $instanceRepository;

    protected function _before()
    {
        $this->instanceRepository = $this->prophesize(InstanceRepository::class);
        $this->serviceManager->setService(InstanceRepository::class, $this->instanceRepository->reveal());

        $this->instanceAttribute = $this->serviceManager->get(InstanceAttribute::class);
    }

    public function testReplace(): void
    {
        $reflectionFunction = new \ReflectionFunction(fn (string $galaxy) => null);
        $this->requestService->getHeader('X-GibsonOs-Token')
            ->shouldBeCalledOnce()
            ->willReturn('marvin')
        ;
        $instance = new Instance();
        $this->instanceRepository->getByToken('marvin')
            ->shouldBeCalledOnce()
            ->willReturn($instance)
        ;

        $this->assertEquals(
            $instance,
            $this->instanceAttribute->replace(new GetInstance(), [], $reflectionFunction->getParameters()[0]),
        );
    }

    public function testReplaceTokenNotFound(): void
    {
        $reflectionFunction = new \ReflectionFunction(fn (string $galaxy) => null);
        $this->requestService->getHeader('X-GibsonOs-Token')
            ->shouldBeCalledOnce()
            ->willReturn('marvin')
        ;
        $this->instanceRepository->getByToken('marvin')
            ->shouldBeCalledOnce()
            ->willThrow(SelectError::class)
        ;

        $this->assertEquals(
            null,
            $this->instanceAttribute->replace(new GetInstance(), [], $reflectionFunction->getParameters()[0]),
        );
    }

    public function testReplaceHeaderNotFound(): void
    {
        $reflectionFunction = new \ReflectionFunction(fn (string $galaxy) => null);
        $this->requestService->getHeader('X-GibsonOs-Token')
            ->shouldBeCalledOnce()
            ->willThrow(RequestError::class)
        ;

        $this->assertEquals(
            null,
            $this->instanceAttribute->replace(new GetInstance(), [], $reflectionFunction->getParameters()[0]),
        );
    }

    public function testWrongAttribute(): void
    {
        $reflectionFunction = new \ReflectionFunction(fn (string $galaxy) => null);

        $this->assertEquals(
            null,
            $this->instanceAttribute->replace(
                $this->prophesize(AttributeInterface::class)->reveal(),
                [],
                $reflectionFunction->getParameters()[0]
            ),
        );
    }
}
