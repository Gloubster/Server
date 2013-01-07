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
    /**
     * @test
     * @expectedException RuntimeException
     */
    public function itShouldFailWithInvalidJsonData()
    {
        $handler = new MessageHandler($this->getStompClient(), $this->getLogger());
        $handler->receive('Hello World');
    }

    /**
     * @test
     * @expectedException RuntimeException
     */
    public function itShouldFailWithInvalidJson()
    {
        $handler = new MessageHandler($this->getStompClient(), $this->getLogger());
        $this->assertFalse($handler->receive('{"Hello": "World"}'));
    }

    /**
     * @test
     * @expectedException RuntimeException
     */
    public function itShouldFailWithInvalidJobData()
    {
        $presence = new WorkerPresence();

        $handler = new MessageHandler($this->getStompClient(), $this->getLogger());
        $this->assertFalse($handler->receive($presence->toJson()));
    }

    /**
     * @test
     * @expectedException RuntimeException
     */
    public function itShouldFailIfStompServerIsNotConnected()
    {
        $job = ImageJob::create('/path/to/source', new DeliveryMock());

        $client = $this->getStompClient();
        $client->expects($this->once())
            ->method('isConnected')
            ->will($this->returnValue(false));

        $handler = new MessageHandler($client, $this->getLogger());
        $handler->receive($job->toJson());
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
        $job = ImageJob::create('/path/to/source', new DeliveryMock(), array('format' => 'jpeg'));

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
        $handler->receive($job->toJson());
    }
}
