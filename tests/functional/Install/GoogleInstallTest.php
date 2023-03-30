<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Middleware\Install;

use GibsonOS\Core\Dto\Install\Configuration;
use GibsonOS\Core\Dto\Install\Input;
use GibsonOS\Module\Middleware\Install\GoogleInstall;
use GibsonOS\Test\Functional\Middleware\MiddlewareFunctionalTest;

class GoogleInstallTest extends MiddlewareFunctionalTest
{
    private GoogleInstall $googleInstall;

    protected function _before(): void
    {
        parent::_before();

        $this->googleInstall = $this->serviceManager->get(GoogleInstall::class);
    }

    public function testInstall(): void
    {
        $install = $this->googleInstall->install('galaxy');

        /** @var Input $input */
        $input = $install->current();
        $this->assertEquals('What is the path of the google application credentials?', $input->getMessage());
        $input->setValue('/arthur/dent/marvin.json');

        $install->next();

        /** @var Input $input */
        $input = $install->current();
        $this->assertEquals('What is the FCM project id?', $input->getMessage());
        $input->setValue('42');

        $install->next();

        /** @var Configuration $configuration */
        $configuration = $install->current();
        $this->assertEquals('Google application configuration generated!', $configuration->getMessage());
        $this->assertEquals(
            [
                'GOOGLE_APPLICATION_CREDENTIALS' => '/arthur/dent/marvin.json',
                'FCM_PROJECT_ID' => '42',
            ],
            $configuration->getValues(),
        );
    }

    public function testGetPart(): void
    {
        $this->assertEquals('config', $this->googleInstall->getPart());
    }

    public function testGetPriority(): void
    {
        $this->assertEquals(800, $this->googleInstall->getPriority());
    }
}
