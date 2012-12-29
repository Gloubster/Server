<?php

namespace Gloubster\Tests\Server\Component;

use Gloubster\Server\GloubsterServer;
use Gloubster\Server\Component\WorkerMonitorBroadcastComponent;
use Gloubster\Tests\GloubsterTest;

class WorkerMonitorBroadcastComponentTest extends GloubsterTest
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
        $server->register(new WorkerMonitorBroadcastComponent());

        // this attach listeners to the stomp server
        $server->activateStompServices(
            $this->getMockBuilder('React\\Stomp\\Client')
                ->disableOriginalConstructor()
                ->getMock()
        );
    }

    public function testEvents()
    {
        $server = $this->getServer();

        $client = $this->getMockBuilder('Predis\\Async\\Client')
                    ->disableOriginalConstructor()
                    ->getMock();

        $conn = $this->getMockBuilder('Predis\Async\Connection\ConnectionInterface')
                    ->disableOriginalConstructor()
                    ->getMock();

        $component = new WorkerMonitorBroadcastComponent();
        $component->register($server);

        $server['dispatcher']->emit('redis-connected', array($server, $client, $conn));
        $server['dispatcher']->emit('stomp-connected', array($server, $server['stomp-client']));
        $server['dispatcher']->emit('boot-connected', array($server));
    }
}
