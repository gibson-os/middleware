<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Service;

use GibsonOS\Core\Attribute\GetEnv;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Module\Middleware\Exception\FcmException;
use Google\Auth\CredentialsLoader as GoogleCredentialsLoader;

class CredentialsLoader
{
    public function __construct(
        #[GetEnv('GOOGLE_APPLICATION_CREDENTIALS')]
        private readonly string $googleCredentialFile,
    ) {
    }

    /**
     * @throws FcmException
     */
    public function getAccessToken(): string
    {
        $googleCredentials = file_get_contents($this->googleCredentialFile);

        if (!$googleCredentials) {
            throw new FcmException('Could not read google credentials file!');
        }

        $credentials = GoogleCredentialsLoader::makeCredentials(
            ['https://www.googleapis.com/auth/cloud-platform'],
            JsonUtility::decode($googleCredentials),
        );
        $authToken = $credentials->fetchAuthToken();

        if (!isset($authToken['access_token'])) {
            throw new FcmException('Access token not in googles oauth response!');
        }

        return $authToken['access_token'];
    }
}
