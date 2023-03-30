<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Middleware\Command\Message;

use DateTime;
use DateTimeImmutable;
use GibsonOS\Core\Dto\Web\Body;
use GibsonOS\Core\Dto\Web\Request;
use GibsonOS\Core\Dto\Web\Response;
use GibsonOS\Core\Enum\Middleware\Message\Vibrate;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Service\EnvService;
use GibsonOS\Module\Middleware\Command\Message\SendCommand;
use GibsonOS\Module\Middleware\Exception\FcmException;
use GibsonOS\Module\Middleware\Model\Instance;
use GibsonOS\Module\Middleware\Model\Message;
use GibsonOS\Module\Middleware\Service\CredentialsLoader;
use GibsonOS\Test\Functional\Middleware\MiddlewareFunctionalTest;
use mysqlDatabase;
use mysqlTable;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class SendCommandTest extends MiddlewareFunctionalTest
{
    private SendCommand $sendCommand;

    private CredentialsLoader|ObjectProphecy $credentialsLoader;

    private DateTimeService|ObjectProphecy $dateTimeService;

    protected function _before(): void
    {
        parent::_before();

        $envService = $this->serviceManager->get(EnvService::class);
        $envService->setString('FCM_PROJECT_ID', 'galaxy');

        $this->credentialsLoader = $this->prophesize(CredentialsLoader::class);
        $this->serviceManager->setService(CredentialsLoader::class, $this->credentialsLoader->reveal());

        $this->dateTimeService = $this->prophesize(DateTimeService::class);
        $this->serviceManager->setService(DateTimeService::class, $this->dateTimeService->reveal());

        $this->sendCommand = $this->serviceManager->get(SendCommand::class);
    }

    public function testExecute(): void
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

        $minusOneSecond = new DateTime('-1 second');
        $this->dateTimeService->get('-1 second')
            ->shouldBeCalledOnce()
            ->willReturn($minusOneSecond)
        ;
        $minusOneHour = new DateTime('-1 hour');
        $this->dateTimeService->get('-1 hour')
            ->shouldBeCalledOnce()
            ->willReturn($minusOneHour)
        ;

        $modelManager->saveWithoutChildren(
            (new Message())
                ->setFcmToken('marvin')
                ->setModule('arthur')
                ->setTask('dent')
                ->setAction('ford')
                ->setInstance($instance)
                ->setToken('no hope')
                ->setTitle('galaxy')
                ->setBody('zaphod')
                ->setData(['prefect' => 'bebblebrox'])
                ->setVibrate(Vibrate::SOS)
        );

        $this->credentialsLoader->getAccessToken()
            ->shouldBeCalledOnce()
            ->willReturn('galaxy')
        ;
        $response = new Response(
            new Request('https://fcm.googleapis.com/v1/projects/messages:send'),
            200,
            [],
            (new Body())->setContent('[]', 2),
            ''
        );
        $this->webService->post(Argument::any())
            ->shouldBeCalledOnce()
            ->willReturn($response)
        ;

        $this->assertEquals(0, $this->sendCommand->execute());

        $messageTable = (new mysqlTable($this->serviceManager->get(mysqlDatabase::class), 'middleware_message'))
            ->setWhere('`id`=?')
            ->addWhereParameter(1)
        ;
        $messageTable->selectPrepared();

        $this->assertNotNull($messageTable->sent->getValue());
        $this->assertNull($messageTable->token->getValue());
        $this->assertNull($messageTable->title->getValue());
        $this->assertNull($messageTable->body->getValue());
        $this->assertEquals('[]', $messageTable->data->getValue());
        $this->assertNull($messageTable->vibrate->getValue());
        $this->assertEquals(0, $messageTable->not_found->getValue());
    }

    public function testExecuteFcmException(): void
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

        $minusOneSecond = new DateTime('-1 second');
        $this->dateTimeService->get('-1 second')
            ->shouldBeCalledOnce()
            ->willReturn($minusOneSecond)
        ;
        $minusOneHour = new DateTime('-1 hour');
        $this->dateTimeService->get('-1 hour')
            ->shouldBeCalledOnce()
            ->willReturn($minusOneHour)
        ;

        $modelManager->saveWithoutChildren(
            (new Message())
                ->setFcmToken('marvin')
                ->setModule('arthur')
                ->setTask('dent')
                ->setAction('ford')
                ->setInstance($instance)
                ->setToken('no hope')
                ->setTitle('galaxy')
                ->setBody('zaphod')
                ->setData(['prefect' => 'bebblebrox'])
                ->setVibrate(Vibrate::SOS)
        );

        $this->credentialsLoader->getAccessToken()
            ->shouldBeCalledOnce()
            ->willReturn('galaxy')
        ;
        $response = new Response(
            new Request('https://fcm.googleapis.com/v1/projects/messages:send'),
            200,
            [],
            (new Body())->setContent('{"error":{"message":"no hope", "code":500}}', 43),
            ''
        );
        $this->webService->post(Argument::any())
            ->shouldBeCalledOnce()
            ->willReturn($response)
        ;

        $message = null;

        try {
            $this->sendCommand->execute();
        } catch (FcmException $exception) {
            $message = $exception->getMessage();
        }

        $this->assertEquals('no hope', $message);

        $messageTable = (new mysqlTable($this->serviceManager->get(mysqlDatabase::class), 'middleware_message'))
            ->setWhere('`id`=?')
            ->addWhereParameter(1)
        ;
        $messageTable->selectPrepared();

        $this->assertNull($messageTable->sent->getValue());
        $this->assertEquals('no hope', $messageTable->token->getValue());
        $this->assertEquals('galaxy', $messageTable->title->getValue());
        $this->assertEquals('zaphod', $messageTable->body->getValue());
        $this->assertEquals('{"prefect":"bebblebrox"}', $messageTable->data->getValue());
        $this->assertEquals('SOS', $messageTable->vibrate->getValue());
        $this->assertEquals(0, $messageTable->not_found->getValue());
    }

    public function testExecuteFcmExceptionNotFound(): void
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

        $minusOneSecond = new DateTime('-1 second');
        $this->dateTimeService->get('-1 second')
            ->shouldBeCalledOnce()
            ->willReturn($minusOneSecond)
        ;
        $minusOneHour = new DateTime('-1 hour');
        $this->dateTimeService->get('-1 hour')
            ->shouldBeCalledOnce()
            ->willReturn($minusOneHour)
        ;

        $modelManager->saveWithoutChildren(
            (new Message())
                ->setFcmToken('marvin')
                ->setModule('arthur')
                ->setTask('dent')
                ->setAction('ford')
                ->setInstance($instance)
                ->setToken('no hope')
                ->setTitle('galaxy')
                ->setBody('zaphod')
                ->setData(['prefect' => 'bebblebrox'])
                ->setVibrate(Vibrate::SOS)
        );

        $this->credentialsLoader->getAccessToken()
            ->shouldBeCalledOnce()
            ->willReturn('galaxy')
        ;
        $response = new Response(
            new Request('https://fcm.googleapis.com/v1/projects/messages:send'),
            200,
            [],
            (new Body())->setContent('{"error":{"message":"no hope", "code":404}}', 43),
            ''
        );
        $this->webService->post(Argument::any())
            ->shouldBeCalledOnce()
            ->willReturn($response)
        ;

        $this->assertEquals(0, $this->sendCommand->execute());

        $messageTable = (new mysqlTable($this->serviceManager->get(mysqlDatabase::class), 'middleware_message'))
            ->setWhere('`id`=?')
            ->addWhereParameter(1)
        ;
        $messageTable->selectPrepared();

        $this->assertNotNull($messageTable->sent->getValue());
        $this->assertNull($messageTable->token->getValue());
        $this->assertNull($messageTable->title->getValue());
        $this->assertNull($messageTable->body->getValue());
        $this->assertEquals('[]', $messageTable->data->getValue());
        $this->assertNull($messageTable->vibrate->getValue());
        $this->assertEquals(1, $messageTable->not_found->getValue());
    }

    public function testExecuteEmpty(): void
    {
        $this->assertEquals(0, $this->sendCommand->execute());
    }

    public function testExecuteReachedSecondLimit(): void
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

        $minusOneSecond = new DateTime('-1 second');
        $this->dateTimeService->get('-1 second')
            ->shouldBeCalledOnce()
            ->willReturn($minusOneSecond)
        ;

        $modelManager->saveWithoutChildren(
            (new Message())
                ->setFcmToken('marvin')
                ->setModule('arthur')
                ->setTask('dent')
                ->setAction('ford')
                ->setInstance($instance)
                ->setToken('no hope')
                ->setTitle('galaxy')
                ->setBody('zaphod')
                ->setData(['prefect' => 'bebblebrox'])
                ->setVibrate(Vibrate::SOS)
        );

        for ($i = 0; $i < 250; ++$i) {
            $modelManager->saveWithoutChildren(
                (new Message())
                    ->setFcmToken('marvin')
                    ->setModule('arthur')
                    ->setTask('dent')
                    ->setAction('ford')
                    ->setInstance($instance)
                    ->setSent(new DateTimeImmutable())
            );
        }

        $this->assertEquals(255, $this->sendCommand->execute());

        $messageTable = (new mysqlTable($this->serviceManager->get(mysqlDatabase::class), 'middleware_message'))
            ->setWhere('`id`=?')
            ->addWhereParameter(1)
        ;
        $messageTable->selectPrepared();

        $this->assertNull($messageTable->sent->getValue());
        $this->assertEquals('no hope', $messageTable->token->getValue());
        $this->assertEquals('galaxy', $messageTable->title->getValue());
        $this->assertEquals('zaphod', $messageTable->body->getValue());
        $this->assertEquals('{"prefect":"bebblebrox"}', $messageTable->data->getValue());
        $this->assertEquals('SOS', $messageTable->vibrate->getValue());
        $this->assertEquals(0, $messageTable->not_found->getValue());
    }

    public function testExecuteReachedHourLimit(): void
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

        $minusOneSecond = new DateTime('-1 second');
        $this->dateTimeService->get('-1 second')
            ->shouldBeCalledOnce()
            ->willReturn($minusOneSecond)
        ;
        $minusOneHour = new DateTime('-1 hour');
        $this->dateTimeService->get('-1 hour')
            ->shouldBeCalledOnce()
            ->willReturn($minusOneHour)
        ;

        $modelManager->saveWithoutChildren(
            (new Message())
                ->setFcmToken('marvin')
                ->setModule('arthur')
                ->setTask('dent')
                ->setAction('ford')
                ->setInstance($instance)
                ->setToken('no hope')
                ->setTitle('galaxy')
                ->setBody('zaphod')
                ->setData(['prefect' => 'bebblebrox'])
                ->setVibrate(Vibrate::SOS)
        );

        for ($i = 0; $i < 1000; ++$i) {
            for ($j = 0; $j < 5; ++$j) {
                $modelManager->saveWithoutChildren(
                    (new Message())
                        ->setFcmToken('marvin')
                        ->setModule('arthur')
                        ->setTask('dent')
                        ->setAction('ford')
                        ->setInstance($instance)
                        ->setSent(new DateTimeImmutable('-' . (3600 - $i) . ' seconds'))
                );
            }
        }

        $this->assertEquals(255, $this->sendCommand->execute());

        $messageTable = (new mysqlTable($this->serviceManager->get(mysqlDatabase::class), 'middleware_message'))
            ->setWhere('`id`=?')
            ->addWhereParameter(1)
        ;
        $messageTable->selectPrepared();

        $this->assertNull($messageTable->sent->getValue());
        $this->assertEquals('no hope', $messageTable->token->getValue());
        $this->assertEquals('galaxy', $messageTable->title->getValue());
        $this->assertEquals('zaphod', $messageTable->body->getValue());
        $this->assertEquals('{"prefect":"bebblebrox"}', $messageTable->data->getValue());
        $this->assertEquals('SOS', $messageTable->vibrate->getValue());
        $this->assertEquals(0, $messageTable->not_found->getValue());
    }
}
