<?php

namespace Gloubster\Tests\Server;

use Gloubster\Tests\GloubsterTest;
use Gloubster\Server\MessageHandler;
use Gloubster\Message\Presence\WorkerPresence;
use Gloubster\Message\Job\ImageJob;
use Gloubster\Delivery\DeliveryMock;

require_once __DIR__ . '/../Mocks/DeliveryMock.php';

class MessageHandlerTest extends GloubsterTest
{
    /** @test */
    public function itShouldFailWithInvalidJsonData()
    {
        $logger = $this->getLogger();
        $logger->expects($this->once())
            ->method('addError');
        $logger->expects($this->never())
            ->method('addInfo');

        $handler = new MessageHandler($this->getStompClient(), $logger);
        $this->assertFalse($handler->receive('Hello World'));
    }

    /** @test */
    public function itShouldFailWithInvalidJson()
    {
        $logger = $this->getLogger();
        $logger->expects($this->once())
            ->method('addError');
        $logger->expects($this->never())
            ->method('addInfo');

        $handler = new MessageHandler($this->getStompClient(), $logger);
        $this->assertFalse($handler->receive('{"Hello": "World"}'));
    }

    /** @test */
    public function itShouldFailWithInvalidJobData()
    {
        $presence = new WorkerPresence();

        $logger = $this->getLogger();
        $logger->expects($this->once())
            ->method('addError');
        $logger->expects($this->never())
            ->method('addInfo');

        $handler = new MessageHandler($this->getStompClient(), $logger);
        $this->assertFalse($handler->receive($presence->toJson()));
    }

    /** @test */
    public function itShouldFailIfStompServerIsNotConnected()
    {
        $job = ImageJob::create('/path/to/source', new DeliveryMock());

        $logger = $this->getLogger();
        $logger->expects($this->once())
            ->method('addError');
        $logger->expects($this->never())
            ->method('addInfo');

        $client = $this->getStompClient();
        $client->expects($this->once())
            ->method('isConnected')
            ->will($this->returnValue(false));

        $handler = new MessageHandler($client, $logger);
        $this->assertFalse($handler->receive($job->toJson()));
    }

    /** @test */
    public function itShouldLogErrors()
    {
        $message = 'bloody exception';
        $exception = new \Exception($message);

        $logger = $this->getLogger();
        $logger->expects($this->once())
            ->method('addError')
            ->with($this->equalTo($message));

        $handler = new MessageHandler($this->getStompClient(), $logger);
        $handler->error($exception);
    }

    /** @test */
    public function itShouldWork()
    {
        $job = ImageJob::create('/path/to/source', new DeliveryMock());

        $logger = $this->getLogger();
        $logger->expects($this->never())
            ->method('addError');
        $logger->expects($this->never())
            ->method('addInfo');

        $client = $this->getStompClient();
        $client->expects($this->once())
            ->method('isConnected')
            ->will($this->returnValue(true));

        $handler = new MessageHandler($client, $logger);
        $this->assertTrue($handler->receive($job->toJson()));
    }
}
