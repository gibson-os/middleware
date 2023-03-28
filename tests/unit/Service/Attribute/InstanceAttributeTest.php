<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Middleware\Service\Attribute;

use Codeception\Test\Unit;
use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\RequestError;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Module\Middleware\Attribute\GetInstance;
use GibsonOS\Module\Middleware\Model\Instance;
use GibsonOS\Module\Middleware\Repository\InstanceRepository;
use GibsonOS\Module\Middleware\Service\Attribute\InstanceAttribute;
use mysqlDatabase;
use mysqlRegistry;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use ReflectionFunction;

class InstanceAttributeTest extends Unit
{
    use ProphecyTrait;

    private InstanceAttribute $instanceAttribute;

    private InstanceRepository|ObjectProphecy $instanceRepository;

    private RequestService|ObjectProphecy $requestService;

    protected function _before()
    {
        $this->instanceRepository = $this->prophesize(InstanceRepository::class);
        $this->requestService = $this->prophesize(RequestService::class);
        mysqlRegistry::getInstance()->set('database', $this->prophesize(mysqlDatabase::class)->reveal());

        $this->instanceAttribute = new InstanceAttribute(
            $this->requestService->reveal(),
            $this->instanceRepository->reveal(),
        );
    }

    public function testReplace(): void
    {
        $reflectionFunction = new ReflectionFunction(fn (string $galaxy) => null);
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
        $reflectionFunction = new ReflectionFunction(fn (string $galaxy) => null);
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
        $reflectionFunction = new ReflectionFunction(fn (string $galaxy) => null);
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
        $reflectionFunction = new ReflectionFunction(fn (string $galaxy) => null);

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
