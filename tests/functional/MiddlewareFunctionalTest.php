<?php
declare(strict_types=1);

namespace GibsonOS\Test\Functional\Middleware;

use GibsonOS\Core\Service\WebService;
use GibsonOS\Test\Functional\Core\FunctionalTest;
use Prophecy\Prophecy\ObjectProphecy;

class MiddlewareFunctionalTest extends FunctionalTest
{
    protected WebService|ObjectProphecy $webService;

    protected function _before(): void
    {
        parent::_before();

        $this->webService = $this->prophesize(WebService::class);
        $this->serviceManager->setService(WebService::class, $this->webService->reveal());
    }

    protected function getDir(): string
    {
        return __DIR__;
    }
}
