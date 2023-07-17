<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Service;

use GibsonOS\Core\Enum\HttpMethod;
use GibsonOS\Core\Exception\RequestError;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\Response\WebResponse;
use GibsonOS\Core\Service\WebService;
use GibsonOS\Module\Middleware\Model\Chromecast\Session;

class ChromecastService
{
    public function __construct(
        private readonly RequestService $requestService,
        private readonly InstanceService $instanceService,
        private readonly WebService $webService,
    ) {
    }

    public function getMiddlewareAction(Session $session, string $action, string $token): WebResponse
    {
        return new WebResponse(
            $this->instanceService->getRequest(
                $session->getInstance(),
                'explorer',
                'middleware',
                $action,
                [
                    'sessionId' => $session->getId(),
                    'token' => $token,
                ],
                HttpMethod::GET,
                $this->getRequestHeaders(),
            ),
            $this->webService,
        );
    }

    private function getRequestHeaders(): array
    {
        $headers = [];

        try {
            $headers['Range'] = $this->requestService->getHeader('Range');
        } catch (RequestError) {
            try {
                $headers['HTTP_RANGE'] = $this->requestService->getHeader('HTTP_RANGE');
            } catch (RequestError) {
                // do nothing
            }
        }

        return $headers;
    }
}
