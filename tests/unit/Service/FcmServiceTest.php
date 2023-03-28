<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Middleware\Service;

use Codeception\Test\Unit;
use GibsonOS\Core\Dto\Web\Body;
use GibsonOS\Core\Dto\Web\Response;
use GibsonOS\Core\Service\WebService;
use GibsonOS\Module\Middleware\Exception\FcmException;
use GibsonOS\Module\Middleware\Model\Message;
use GibsonOS\Module\Middleware\Service\CredentialsLoader;
use GibsonOS\Module\Middleware\Service\FcmService;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

class FcmServiceTest extends Unit
{
    use ProphecyTrait;

    private FcmService $fcmService;

    private WebService|ObjectProphecy $webService;

    private LoggerInterface|ObjectProphecy $logger;

    private CredentialsLoader|ObjectProphecy $credentialsLoader;

    public function _before()
    {
        $this->webService = $this->prophesize(WebService::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->credentialsLoader = $this->prophesize(CredentialsLoader::class);

        $this->fcmService = new FcmService(
            'galaxy',
            $this->webService->reveal(),
            $this->logger->reveal(),
            $this->credentialsLoader->reveal(),
        );
    }

    public function testPushMessage(): void
    {
        $this->credentialsLoader->getAccessToken()
            ->shouldBeCalledOnce()
            ->willReturn('galaxy')
        ;
        $response = $this->prophesize(Response::class);
        $this->webService->post(Argument::any())
            ->shouldBeCalledOnce()
            ->willReturn($response->reveal())
        ;
        $body = $this->prophesize(Body::class);
        $response->getBody()
            ->shouldBeCalledOnce()
            ->willReturn($body->reveal())
        ;
        $body->getContent()
            ->shouldBeCalledOnce()
            ->willReturn('"marvin"')
        ;

        $this->fcmService->pushMessage(
            (new Message())
                ->setFcmToken('ford')
                ->setModule('arthur')
                ->setTask('dent')
                ->setAction('prefect')
        );
    }

    public function testPushMessageWithError(): void
    {
        $this->credentialsLoader->getAccessToken()
            ->shouldBeCalledOnce()
            ->willReturn('galaxy')
        ;
        $response = $this->prophesize(Response::class);
        $this->webService->post(Argument::any())
            ->shouldBeCalledOnce()
            ->willReturn($response->reveal())
        ;
        $body = $this->prophesize(Body::class);
        $response->getBody()
            ->shouldBeCalledOnce()
            ->willReturn($body->reveal())
        ;
        $body->getContent()
            ->shouldBeCalledOnce()
            ->willReturn('{"error": {"message": "marvin", "code": 42}}')
        ;

        $this->expectException(FcmException::class);
        $this->fcmService->pushMessage(
            (new Message())
                ->setFcmToken('ford')
                ->setModule('arthur')
                ->setTask('dent')
                ->setAction('prefect')
        );
    }
}
