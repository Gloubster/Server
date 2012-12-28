<?php

namespace Gloubster\Tests\Server;

use Gloubster\Configuration;
use Gloubster\Message\Job\ImageJob;
use Gloubster\Message\Presence\WorkerPresence;
use Gloubster\Server\GloubsterServer;
use Gloubster\Server\Component\ComponentInterface;
use Gloubster\Server\Component\StopComponent;

require_once __DIR__ . '/../Mocks/DeliveryMock.php';

class GloubsterServerTest extends \PHPUnit_Framework_TestCase
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

        $client = $this->getMockBuilder('React\Stomp\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $server->activateStompServices($client);

        $this->assertTrue($component->registered);
        $this->assertFalse($component->Redisregistered);
        $this->assertTrue($component->STOMPregistered);
    }

    /**
     * @covers Gloubster\Server\GloubsterServer::activateRedisServices
     */
    public function testActivateRedisServices()
    {
        $component = new TestComponent();

        $server = $this->getServer();
        $server->register($component);

        $client = $this->getMockBuilder('Predis\Async\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $conn = $this->getMockBuilder('Predis\Async\Connection\ConnectionInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $server->activateRedisServices($client, $conn);

        $this->assertTrue($component->registered);
        $this->assertFalse($component->STOMPregistered);
        $this->assertTrue($component->Redisregistered);
    }

    /**
     * @covers Gloubster\Server\GloubsterServer::run
     */
    public function testRun()
    {
        $logger = $this->getMockBuilder('Monolog\Logger')
            ->disableOriginalConstructor()
            ->getMock();

        $conf = new Configuration(file_get_contents(__DIR__ . '/../../../resources/config.json'));

        $server = GloubsterServer::create(\React\EventLoop\Factory::create(), $conf, $logger);

        $server->register(new StopComponent());
        $server->run();
    }

    /**
     * @covers Gloubster\Server\GloubsterServer::incomingMessage
     */
    public function testIncomingMessage()
    {
        $server = $this->getServer();

        $message = ImageJob::create('/path/to/source', new \Gloubster\Delivery\DeliveryMock('cool-id'));

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

        $that = $this;
        $data = 'no-json data';
        $server['monolog']->expects($this->once())
            ->method('addError')
            ->will($this->returnCallback(function ($message) use ($that, $data) {
                        $that->assertGreaterThan(0, strpos($message, $data));
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

        $that = $this;
        $data = '{"hello": "world !"}';
        $server['monolog']->expects($this->once())
            ->method('addError')
            ->will($this->returnCallback(function ($message) use ($that, $data) {
                        $that->assertGreaterThan(0, strpos($message, $data));
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

        $that = $this;

        $worker = new WorkerPresence();

        $data = $worker->toJson();
        $server['monolog']->expects($this->once())
            ->method('addError')
            ->will($this->returnCallback(function ($message) use ($that, $data) {
                        $that->assertGreaterThan(0, strpos($message, $data));
                    }));

        $server->incomingMessage($data);
    }

    /**
     * @covers Gloubster\Server\GloubsterServer::incomingMessage
     */
    public function testIncomingMessageWithoutStompConnection()
    {
        $server = $this->getServer();

        $message = ImageJob::create('/path/to/source', new \Gloubster\Delivery\DeliveryMock('cool-id'));

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
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $conf = new Configuration(file_get_contents(__DIR__ . '/../../../resources/config.json'));

        $logger = $this->getMockBuilder('Monolog\Logger')
            ->disableOriginalConstructor()
            ->getMock();

        GloubsterServer::create($loop, $conf, $logger);
    }

    private function getServer()
    {
        $ws = $this->getMockBuilder('Gloubster\Server\WebsocketApplication')
            ->disableOriginalConstructor()
            ->getMock();

        $client = $this->getMockBuilder('React\Stomp\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $conf = new Configuration(file_get_contents(__DIR__ . '/../../../resources/config.json'));

        $logger = $this->getMockBuilder('Monolog\Logger')
            ->disableOriginalConstructor()
            ->getMock();

        return new GloubsterServer($ws, $client, $loop, $conf, $logger);
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

    public function register(GloubsterServer $server)
    {
        $this->registered = true;
    }

    public function registerSTOMP(GloubsterServer $server, \React\Stomp\Client $stomp)
    {
        $this->STOMPregistered = true;
    }

    public function registerRedis(GloubsterServer $server, \Predis\Async\Client $client, \Predis\Async\Connection\ConnectionInterface $conn)
    {
        $this->Redisregistered = true;
    }

    public function boot(GloubsterServer $server)
    {
    }
}