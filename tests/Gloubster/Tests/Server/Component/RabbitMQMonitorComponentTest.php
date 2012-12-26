<?php

namespace Gloubster\Tests\Server\Component;

use Gloubster\Server\GloubsterServer;
use Gloubster\Server\Component\RabbitMQMonitorComponent;

class RabbitMQMonitorComponentTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function itShouldRegister()
    {
        $websocket = $this->getMockBuilder('Gloubster\\Server\\WebsocketApplication')
            ->disableOriginalConstructor()
            ->getMock();

        $client = $this->getMockBuilder('React\\Stomp\\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $loop = $this->getMockBuilder('React\\EventLoop\\LoopInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $this->getMockBuilder('Monolog\\Logger')
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
            "session-server": {
                "type": "memcache",
                "host": "localhost",
                "port": 11211
            },
            "websocket-server": {
                "address": "local.gloubster",
                "port": 9990
            },
            "listeners": []
        }
        ');

        $loop->expects($this->once())
            ->method('addPeriodicTimer')
            ->with($this->greaterThan(0), $this->anything());

        $component = new RabbitMQMonitorComponent();

        $server = new GloubsterServer($websocket, $client, $loop, $conf, $logger);
        $server->register($component);

        $component->fetchMQInformations($websocket, $conf);
    }
}
