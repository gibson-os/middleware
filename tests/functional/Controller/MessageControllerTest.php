<?php
declare(strict_types=1);

namespace functional\Controller;

use DateTimeImmutable;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Module\Middleware\Controller\MessageController;
use GibsonOS\Module\Middleware\Model\Instance;
use GibsonOS\Module\Middleware\Model\Message;
use GibsonOS\Module\Middleware\Repository\MessageRepository;
use GibsonOS\Test\Functional\Middleware\MiddlewareFunctionalTest;

class MessageControllerTest extends MiddlewareFunctionalTest
{
    private MessageController $messageController;

    protected function _before(): void
    {
        parent::_before();

        $this->messageController = $this->serviceManager->get(MessageController::class);
    }

    public function testPush(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $instance = (new Instance())
            ->setUser($this->addUser())
            ->setUrl('http://arthur.dent/')
            ->setToken('ford')
            ->setSecret('prefect')
            ->setExpireDate(new DateTimeImmutable('+1 hour'))
        ;
        $modelManager->saveWithoutChildren($instance);
        $message = (new Message())
            ->setFcmToken('galaxy')
            ->setModule('arthur')
            ->setTask('dent')
            ->setAction('ford')
        ;

        $this->checkSuccessResponse(
            $this->messageController->push(
                $this->serviceManager->get(MessageRepository::class),
                $modelManager,
                $message,
                $instance,
            )
        );
    }

    public function testPushFcmTokenNotFound(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $instance = (new Instance())
            ->setUser($this->addUser())
            ->setUrl('http://arthur.dent/')
            ->setToken('ford')
            ->setSecret('prefect')
            ->setExpireDate(new DateTimeImmutable('+1 hour'))
        ;
        $modelManager->saveWithoutChildren($instance);
        $message = (new Message())
            ->setFcmToken('galaxy')
            ->setModule('arthur')
            ->setTask('dent')
            ->setAction('ford')
        ;
        $modelManager->saveWithoutChildren(
            (new Message())
                ->setFcmToken('galaxy')
                ->setModule('arthur')
                ->setTask('dent')
                ->setAction('ford')
                ->setSent(new DateTimeImmutable())
                ->setNotFound(true)
                ->setInstance($instance)
        );

        $this->checkErrorResponse(
            $this->messageController->push(
                $this->serviceManager->get(MessageRepository::class),
                $modelManager,
                $message,
                $instance,
            ),
            'FCM token not found',
        );
    }

    public function testPushFcmTokenWithOldMessage(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $instance = (new Instance())
            ->setUser($this->addUser())
            ->setUrl('http://arthur.dent/')
            ->setToken('ford')
            ->setSecret('prefect')
            ->setExpireDate(new DateTimeImmutable('+1 hour'))
        ;
        $modelManager->saveWithoutChildren($instance);
        $message = (new Message())
            ->setFcmToken('galaxy')
            ->setModule('arthur')
            ->setTask('dent')
            ->setAction('ford')
        ;
        $modelManager->saveWithoutChildren(
            (new Message())
                ->setFcmToken('galaxy')
                ->setModule('arthur')
                ->setTask('dent')
                ->setAction('ford')
                ->setSent(new DateTimeImmutable())
                ->setInstance($instance)
        );

        $this->checkSuccessResponse(
            $this->messageController->push(
                $this->serviceManager->get(MessageRepository::class),
                $modelManager,
                $message,
                $instance,
            )
        );
    }
}
