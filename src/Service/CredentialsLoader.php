<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Service;

use GibsonOS\Core\Attribute\GetEnv;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Module\Middleware\Exception\FcmException;
use Google\Auth\CredentialsLoader as GoogleCredentialsLoader;
use JsonException;

class CredentialsLoader
{
    public function __construct(
        #[GetEnv('GOOGLE_APPLICATION_CREDENTIALS')] private readonly string $googleCredentialFile,
    ) {
    }

    /**
     * @throws FcmException
     * @throws JsonException
     */
    public function getAccessToken(): string
    {
        $credentials = GoogleCredentialsLoader::makeCredentials(
            ['https://www.googleapis.com/auth/cloud-platform'],
            JsonUtility::decode(file_get_contents($this->googleCredentialFile))
        );
        $authToken = $credentials->fetchAuthToken();

        if (!isset($authToken['access_token'])) {
            throw new FcmException('Access token not in googles oauth response!');
        }

        return $authToken['access_token'];
    }
}
