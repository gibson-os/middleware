<?php
declare(strict_types=1);

namespace GibsonOS\Test\Functional\Middleware\Install;

use GibsonOS\Core\Dto\Install\Configuration;
use GibsonOS\Core\Dto\Install\Input;
use GibsonOS\Module\Middleware\Install\ChromecastReceiverAppIdInstall;
use GibsonOS\Test\Functional\Middleware\MiddlewareFunctionalTest;

class ChromecastReceiverAppIdInstallTest extends MiddlewareFunctionalTest
{
    private ChromecastReceiverAppIdInstall $chromecastReceiverAppIdInstall;

    protected function _before(): void
    {
        parent::_before();

        $this->chromecastReceiverAppIdInstall = $this->serviceManager->get(ChromecastReceiverAppIdInstall::class);
    }

    public function testInstall(): void
    {
        $install = $this->chromecastReceiverAppIdInstall->install('galaxy');

        /** @var Input $input */
        $input = $install->current();
        $this->assertEquals('What is the Chromecast receiver app id?', $input->getMessage());
        $input->setValue('galaxy42');

        $install->next();

        /** @var Configuration $configuration */
        $configuration = $install->current();
        $this->assertEquals('Chromecast configuration generated!', $configuration->getMessage());
        $this->assertEquals(
            ['CHROMECAST_RECEIVER_APP_ID' => 'galaxy42'],
            $configuration->getValues(),
        );
    }

    public function testGetPart(): void
    {
        $this->assertEquals('config', $this->chromecastReceiverAppIdInstall->getPart());
    }

    public function testGetPriority(): void
    {
        $this->assertEquals(0, $this->chromecastReceiverAppIdInstall->getPriority());
    }
}
