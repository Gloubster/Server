<?php

namespace Gloubster\Tests\Server;

use Gloubster\Message\Job\ImageJob;
use Gloubster\Message\Presence\WorkerPresence;
use Gloubster\Server\GloubsterServer;
use Gloubster\Server\GloubsterServerInterface;
use Gloubster\Server\Component\ComponentInterface;
use Gloubster\Server\Component\StopComponent;
use Gloubster\Tests\GloubsterTest;
use Gloubster\Delivery\DeliveryMock;
use React\EventLoop\Factory as LoopFactory;

require_once __DIR__ . '/../Mocks/DeliveryMock.php';

/**
 * @covers Gloubster\Server\GloubsterServer
 */
class GloubsterServerTest extends GloubsterTest
{
    /**
     * @covers Gloubster\Server\GloubsterServer::__construct
     * @covers Gloubster\Server\GloubsterServer::register
     */
    public function testRegister()
    {
        $component = new TestComponent();

        $server = $this->getServer();
        $server->register($component);

        $this->assertTrue($component->registered);
        $this->assertFalse($component->STOMPregistered);
        $this->assertFalse($component->Redisregistered);
    }

    /**
     * @covers Gloubster\Server\GloubsterServer::activateStompServices
     */
    public function testActivateStompServices()
    {
        $component = new TestComponent();

        $server = $this->getServer();
        $server->register($component);

        $server->activateStompServices($server['stomp-client']);

        $this->assertTrue($component->registered);
        $this->assertFalse($component->Redisregistered);
        $this->assertTrue($component->STOMPregistered);
    }

    /**
     * @covers Gloubster\Server\GloubsterServer::run
     */
    public function testRun()
    {
        $logger = $this->getLogger();
        $conf = $this->getTestConfiguration();
        $this->getSessionServer($conf);

        $server = GloubsterServer::create(LoopFactory::create(), $conf, $logger);

        $server->register(new StopComponent());
        $server->run();
    }

    /**
     * @covers Gloubster\Server\GloubsterServer::incomingMessage
     */
    public function testIncomingMessage()
    {
        $server = $this->getServer();

        $message = ImageJob::create('/path/to/source', new DeliveryMock('cool-id'));

        $server['stomp-client']->expects($this->any())
            ->method('isConnected')
            ->will($this->returnValue(true));

        $server['stomp-client']->expects($this->once())
            ->method('send')
            ->with($this->equalTo('/exchange/phrasea.subdef.dispatcher'), $message->toJson());

        $server->incomingMessage($message->toJson());
    }

    /**
     * @covers Gloubster\Server\GloubsterServer::incomingMessage
     */
    public function testWrongIncomingMessage()
    {
        $server = $this->getServer();

        $server['stomp-client']->expects($this->never())
            ->method('send');

        $phpunit = $this;
        $data = 'no-json data';
        $server['monolog']->expects($this->once())
            ->method('addError')
            ->will($this->returnCallback(function ($message) use ($phpunit, $data) {
                        $phpunit->assertGreaterThan(0, strpos($message, $data));
                    }));

        $server->incomingMessage($data);
    }

    /**
     * @covers Gloubster\Server\GloubsterServer::incomingMessage
     */
    public function testWrongJsonMessage()
    {
        $server = $this->getServer();

        $server['stomp-client']->expects($this->never())
            ->method('send');

        $phpunit = $this;
        $data = '{"hello": "world !"}';
        $server['monolog']->expects($this->once())
            ->method('addError')
            ->will($this->returnCallback(function ($message) use ($phpunit, $data) {
                        $phpunit->assertGreaterThan(0, strpos($message, $data));
                    }));

        $server->incomingMessage($data);
    }

    /**
     * @covers Gloubster\Server\GloubsterServer::incomingMessage
     */
    public function testNonJobMessage()
    {
        $server = $this->getServer();

        $server['stomp-client']->expects($this->never())
            ->method('send');

        $phpunit = $this;

        $worker = new WorkerPresence();

        $data = $worker->toJson();
        $server['monolog']->expects($this->once())
            ->method('addError')
            ->will($this->returnCallback(function ($message) use ($phpunit, $data) {
                        $phpunit->assertGreaterThan(0, strpos($message, $data));
                    }));

        $server->incomingMessage($data);
    }

    /**
     * @covers Gloubster\Server\GloubsterServer::incomingMessage
     */
    public function testIncomingMessageWithoutStompConnection()
    {
        $server = $this->getServer();

        $message = ImageJob::create('/path/to/source', new DeliveryMock('cool-id'));

        $server['stomp-client']->expects($this->any())
            ->method('isConnected')
            ->will($this->returnValue(false));

        $server['stomp-client']->expects($this->never())
            ->method('send');

        $server['monolog']->expects($this->once())
            ->method('addError')
            ->with($this->equalTo('STOMP server not yet connected'));

        $server->incomingMessage($message->toJson());
    }

    /**
     * @covers Gloubster\Server\GloubsterServer::incomingError
     */
    public function testIncomingError()
    {
        $server = $this->getServer();
        $exception = new \InvalidArgumentException('SHIT');

        $server['monolog']->expects($this->once())
            ->method('addError')
            ->with($this->equalTo($exception->getMessage()));

        $server->incomingError($exception);
    }

    /**
     * @covers Gloubster\Server\GloubsterServer::logError
     */
    public function testLogError()
    {
        $server = $this->getServer();
        $exception = new \InvalidArgumentException('SHIT');

        $server['monolog']->expects($this->once())
            ->method('addError')
            ->with($this->equalTo($exception->getMessage()));

        $server->logError($exception);
    }

    /**
     * @covers Gloubster\Server\GloubsterServer::throwError
     */
    public function testThrowError()
    {
        $server = $this->getServer();
        $exception = new \InvalidArgumentException('SHIT');

        $server['monolog']->expects($this->once())
            ->method('addError')
            ->with($this->equalTo($exception->getMessage()));

        try {
            $server->throwError($exception);
            $this->fail('Should have raised an exception');
        } catch (\Exception $e) {
            $this->assertEquals($exception, $e);
        }
    }

    /**
     * @covers Gloubster\Server\GloubsterServer::create
     */
    public function testCreate()
    {
        $loop = $this->getEventLoop();
        $conf = $this->getTestConfiguration();
        $logger = $this->getLogger();

        GloubsterServer::create($loop, $conf, $logger);
    }
}

class TestComponent implements ComponentInterface
{
    public $registered;
    public $STOMPregistered;
    public $Redisregistered;

    public function __construct()
    {
        $this->registered = false;
        $this->STOMPregistered = false;
        $this->Redisregistered = false;
    }

    public function register(GloubsterServerInterface $server)
    {
        $component = $this;
        $this->registered = true;

        $server['dispatcher']->on('stomp-connected', function () use ($component) {
            $component->STOMPregistered = true;
        });

        $server['dispatcher']->on('redis-connected', function () use ($component) {
            $component->Redisregistered = true;
        });
    }
}
