<?php
declare(strict_types=1);

namespace GibsonOS\Test\Functional\Middleware\Command\Message;

use DateTime;
use DateTimeImmutable;
use GibsonOS\Core\Dto\Web\Body;
use GibsonOS\Core\Dto\Web\Request;
use GibsonOS\Core\Dto\Web\Response;
use GibsonOS\Core\Enum\HttpStatusCode;
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
use MDO\Client;
use MDO\Dto\Query\Where;
use MDO\Dto\Record;
use MDO\Dto\Table;
use MDO\Manager\TableManager;
use MDO\Query\SelectQuery;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class SendCommandTest extends MiddlewareFunctionalTest
{
    private SendCommand $sendCommand;

    private CredentialsLoader|ObjectProphecy $credentialsLoader;

    private DateTimeService|ObjectProphecy $dateTimeService;

    private Table $table;

    private Client $client;

    protected function _before(): void
    {
        parent::_before();

        $envService = $this->serviceManager->get(EnvService::class);
        $envService->setString('FCM_PROJECT_ID', 'galaxy');

        $this->credentialsLoader = $this->prophesize(CredentialsLoader::class);
        $this->serviceManager->setService(CredentialsLoader::class, $this->credentialsLoader->reveal());

        $this->dateTimeService = $this->prophesize(DateTimeService::class);
        $this->serviceManager->setService(DateTimeService::class, $this->dateTimeService->reveal());

        $this->client = $this->serviceManager->get(Client::class);
        $tableManager = $this->serviceManager->get(TableManager::class);
        $this->table = $tableManager->getTable('middleware_message');

        $this->sendCommand = $this->serviceManager->get(SendCommand::class);
    }

    public function testExecute(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $instance = (new Instance($this->modelWrapper))
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
            (new Message($this->modelWrapper))
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
            HttpStatusCode::OK,
            [],
            (new Body())->setContent('[]', 2),
            ''
        );
        $this->webService->post(Argument::any())
            ->shouldBeCalledOnce()
            ->willReturn($response)
        ;

        $this->assertEquals(0, $this->sendCommand->execute());

        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where('`id`=?', [1]))
        ;
        $result = $this->client->execute($selectQuery);
        /** @var Record $record */
        $record = $result->iterateRecords()->current();

        $this->assertNotNull($record->get('sent')->getValue());
        $this->assertNull($record->get('token')->getValue());
        $this->assertNull($record->get('title')->getValue());
        $this->assertNull($record->get('body')->getValue());
        $this->assertEquals('[]', $record->get('data')->getValue());
        $this->assertNull($record->get('vibrate')->getValue());
        $this->assertEquals(0, $record->get('not_found')->getValue());
    }

    public function testExecuteFcmException(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $instance = (new Instance($this->modelWrapper))
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
            (new Message($this->modelWrapper))
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
            HttpStatusCode::OK,
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

        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where('`id`=?', [1]))
        ;
        $result = $this->client->execute($selectQuery);
        /** @var Record $record */
        $record = $result->iterateRecords()->current();

        $this->assertNull($record->get('sent')->getValue());
        $this->assertEquals('no hope', $record->get('token')->getValue());
        $this->assertEquals('galaxy', $record->get('title')->getValue());
        $this->assertEquals('zaphod', $record->get('body')->getValue());
        $this->assertEquals('{"prefect":"bebblebrox"}', $record->get('data')->getValue());
        $this->assertEquals('SOS', $record->get('vibrate')->getValue());
        $this->assertEquals(0, $record->get('not_found')->getValue());
    }

    public function testExecuteFcmExceptionNotFound(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $instance = (new Instance($this->modelWrapper))
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
            (new Message($this->modelWrapper))
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
            HttpStatusCode::OK,
            [],
            (new Body())->setContent('{"error":{"message":"no hope", "code":404}}', 43),
            ''
        );
        $this->webService->post(Argument::any())
            ->shouldBeCalledOnce()
            ->willReturn($response)
        ;

        $this->assertEquals(0, $this->sendCommand->execute());

        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where('`id`=?', [1]))
        ;
        $result = $this->client->execute($selectQuery);
        /** @var Record $record */
        $record = $result->iterateRecords()->current();

        $this->assertNotNull($record->get('sent')->getValue());
        $this->assertNull($record->get('token')->getValue());
        $this->assertNull($record->get('title')->getValue());
        $this->assertNull($record->get('body')->getValue());
        $this->assertEquals('[]', $record->get('data')->getValue());
        $this->assertNull($record->get('vibrate')->getValue());
        $this->assertEquals(1, $record->get('not_found')->getValue());
    }

    public function testExecuteEmpty(): void
    {
        $this->assertEquals(0, $this->sendCommand->execute());
    }

    public function testExecuteReachedSecondLimit(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $instance = (new Instance($this->modelWrapper))
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
            (new Message($this->modelWrapper))
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
                (new Message($this->modelWrapper))
                    ->setFcmToken('marvin')
                    ->setModule('arthur')
                    ->setTask('dent')
                    ->setAction('ford')
                    ->setInstance($instance)
                    ->setSent(new DateTimeImmutable())
            );
        }

        $this->assertEquals(255, $this->sendCommand->execute());

        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where('`id`=?', [1]))
        ;
        $result = $this->client->execute($selectQuery);
        /** @var Record $record */
        $record = $result->iterateRecords()->current();

        $this->assertNull($record->get('sent')->getValue());
        $this->assertEquals('no hope', $record->get('token')->getValue());
        $this->assertEquals('galaxy', $record->get('title')->getValue());
        $this->assertEquals('zaphod', $record->get('body')->getValue());
        $this->assertEquals('{"prefect":"bebblebrox"}', $record->get('data')->getValue());
        $this->assertEquals('SOS', $record->get('vibrate')->getValue());
        $this->assertEquals(0, $record->get('not_found')->getValue());
    }

    public function testExecuteReachedHourLimit(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $instance = (new Instance($this->modelWrapper))
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
            (new Message($this->modelWrapper))
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
                    (new Message($this->modelWrapper))
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

        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where('`id`=?', [1]))
        ;
        $result = $this->client->execute($selectQuery);
        /** @var Record $record */
        $record = $result->iterateRecords()->current();

        $this->assertNull($record->get('sent')->getValue());
        $this->assertEquals('no hope', $record->get('token')->getValue());
        $this->assertEquals('galaxy', $record->get('title')->getValue());
        $this->assertEquals('zaphod', $record->get('body')->getValue());
        $this->assertEquals('{"prefect":"bebblebrox"}', $record->get('data')->getValue());
        $this->assertEquals('SOS', $record->get('vibrate')->getValue());
        $this->assertEquals(0, $record->get('not_found')->getValue());
    }
}
