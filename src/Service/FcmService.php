<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Service;

use GibsonOS\Core\Attribute\GetEnv;
use GibsonOS\Core\Dto\Web\Body;
use GibsonOS\Core\Dto\Web\Request;
use GibsonOS\Core\Exception\WebException;
use GibsonOS\Core\Service\WebService;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Module\Middleware\Exception\FcmException;
use GibsonOS\Module\Middleware\Model\Message;
use JsonException;
use Psr\Log\LoggerInterface;

class FcmService
{
    private const URL = 'https://fcm.googleapis.com/v1/projects/';

    private string $url;

    public function __construct(
        #[GetEnv('FCM_PROJECT_ID')] private readonly string $projectId,
        private readonly WebService $webService,
        private readonly LoggerInterface $logger,
        private readonly CredentialsLoader $credentialsLoader,
    ) {
        $this->url = self::URL . $this->projectId . '/';
    }

    /**
     * @throws WebException
     * @throws FcmException
     * @throws JsonException
     */
    public function pushMessage(Message $message): FcmService
    {
        $content = JsonUtility::encode(['message' => $message]);
        $request = (new Request($this->url . 'messages:send'))
            ->setHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->credentialsLoader->getAccessToken(),
            ])
            ->setBody((new Body())->setContent($content, mb_strlen($content)))
        ;

        $response = $this->webService->post($request);
        $body = $response->getBody()->getContent();
        $this->logger->debug(sprintf('FCM push response: %s', $body));
        $body = JsonUtility::decode($body);

        if (isset($body['error'])) {
            throw new FcmException($body['error']['message'], $body['error']['code']);
        }

        return $this;
    }
}
