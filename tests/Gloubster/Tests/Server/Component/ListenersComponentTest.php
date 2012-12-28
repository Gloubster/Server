<?php

namespace Gloubster\Tests\Server\Component;

use Gloubster\Server\GloubsterServer;
use Gloubster\Server\GloubsterServerInterface;
use Gloubster\Server\Component\ListenersComponent;
use Gloubster\Server\Listener\JobListenerInterface;
use React\EventLoop\LoopInterface;

class ListenersComponentTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function itShouldRegister()
    {
        $websocket = $this->getMockBuilder('Gloubster\Server\WebsocketApplication')
            ->disableOriginalConstructor()
            ->getMock();

        $client = $this->getMockBuilder('React\Stomp\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $this->getMockBuilder('Monolog\Logger')
            ->disableOriginalConstructor()
            ->getMock();

        $conf = new \Gloubster\Configuration('{
            "server": {
                "host": "localhost",
                "port": 5672,
                "user": "guest",
                "password": "guest",
                "vhost": "/",
                "server-management": {
                    "port": 55672,
                    "scheme": "http"
                },
                "stomp-gateway": {
                    "port": 61613
                }
            },
            "redis-server": {
                "host": "localhost",
                "port": 6379
            },
            "session-server": {
                "type": "memcache",
                "host": "localhost",
                "port": 11211
            },
            "websocket-server": {
                "address": "local.gloubster",
                "port": 9990
            },
            "listeners": [
                {
                    "type": "' . str_replace('\\', '\\\\', __NAMESPACE__) . '\\\\ListenerTester",
                    "options": {
                        "transport": "tcp",
                        "address": "0.0.0.0",
                        "port": 22345
                    }
                }
            ]
        }');

        $loop->created = null;

        $server = new GloubsterServer($websocket, $client, $loop, $conf, $logger);

        $server['test-token'] = false;
        $server->register(new ListenersComponent());

        $this->assertFalse($server['test-token']);
        // this attach listeners to the stomp server
        $server->activateStompServices(
            $this->getMockBuilder('React\\Stomp\\Client')
                ->disableOriginalConstructor()
                ->getMock()
        );
        $this->assertTrue($server['test-token']);
    }

    public function testThatRegisterRedisDoesNotThrowError()
    {
        $server = $this->getMockBuilder('Gloubster\\Server\\GloubsterServer')
                    ->disableOriginalConstructor()
                    ->getMock();

        $client = $this->getMockBuilder('Predis\\Async\\Client')
                    ->disableOriginalConstructor()
                    ->getMock();

        $conn = $this->getMockBuilder('Predis\Async\Connection\ConnectionInterface')
                    ->disableOriginalConstructor()
                    ->getMock();

        $component = new ListenersComponent();
        $component->registerRedis($server, $client, $conn);
    }
}

class ListenerTester implements JobListenerInterface
{
    public function attach(GloubsterServerInterface $server)
    {
        $server['test-token'] = true;
    }

    public static function create(LoopInterface $loop, array $options)
    {
        $loop->created = $options;
        return new static();
    }
}
