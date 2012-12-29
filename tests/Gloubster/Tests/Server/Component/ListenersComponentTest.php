<?php

namespace Gloubster\Tests\Server\Component;

use Gloubster\Exception\RuntimeException;
use Gloubster\Server\GloubsterServer;
use Gloubster\Server\GloubsterServerInterface;
use Gloubster\Server\Component\ListenersComponent;
use Gloubster\Server\Listener\JobListenerInterface;
use Gloubster\Tests\GloubsterTest;
use Monolog\Logger;
use React\EventLoop\LoopInterface;

class ListenersComponentTest extends GloubsterTest
{
    /** @test */
    public function itShouldRegister()
    {
        $server = $this->getServer();

        $server['monolog']->expects($this->never())
            ->method('addError');

        $server['configuration'] = new \Gloubster\Configuration('{
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
                "host": "127.0.0.1",
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


    /**
     * @test
     */
    public function itShouldFailWhenRegisteringInvalidClassnames()
    {
        $server = $this->getServer();

        $server['monolog'] = $this->getMockBuilder('Monolog\\Logger')
            ->disableOriginalConstructor()
            ->getMock();

        $server['monolog']->expects($this->once())
            ->method('addError');

        $server['configuration'] = new \Gloubster\Configuration('{
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
                    "type": "InvalidNamespace\\\\Listener",
                    "options": {
                        "transport": "tcp",
                        "address": "0.0.0.0",
                        "port": 22345
                    }
                }
            ]
        }');

        $server->register(new ListenersComponent());

        // this attach listeners to the stomp server
        $server->activateStompServices(
            $this->getMockBuilder('React\\Stomp\\Client')
                ->disableOriginalConstructor()
                ->getMock()
        );
    }


    /**
     * @test
     */
    public function itShouldFailWhenRegisteringInvalidListener()
    {
        $server = $this->getServer();

        $server['monolog'] = $this->getMockBuilder('Monolog\\Logger')
            ->disableOriginalConstructor()
            ->getMock();

        $server['monolog']->expects($this->once())
            ->method('addError');

        $server['configuration'] = new \Gloubster\Configuration('{
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
                    "type": "Gloubster\\\\Communication",
                    "options": {
                        "transport": "tcp",
                        "address": "0.0.0.0",
                        "port": 22345
                    }
                }
            ]
        }');

        $server->register(new ListenersComponent());

        // this attach listeners to the stomp server
        $server->activateStompServices(
            $this->getMockBuilder('React\\Stomp\\Client')
                ->disableOriginalConstructor()
                ->getMock()
        );
    }

    /**
     * @test
     */
    public function itShouldLogErrorIfListenerBuildFails()
    {
        $server = $this->getServer();

        $server['monolog'] = $this->getMockBuilder('Monolog\\Logger')
            ->disableOriginalConstructor()
            ->getMock();

        $server['monolog']->expects($this->once())
            ->method('addError');

        $server['configuration'] = new \Gloubster\Configuration('{
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
                    "type": "' . str_replace('\\', '\\\\', __NAMESPACE__) . '\\\\ListenerFailTester",
                    "options": {
                        "transport": "tcp",
                        "address": "0.0.0.0",
                        "port": 22345
                    }
                }
            ]
        }');

        $server->register(new ListenersComponent());

        // this attach listeners to the stomp server
        $server->activateStompServices(
            $this->getMockBuilder('React\\Stomp\\Client')
                ->disableOriginalConstructor()
                ->getMock()
        );
    }

    protected function getServer()
    {
        $server = parent::getServer();
        $server['configuration'] = new \Gloubster\Configuration('{
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
            "listeners": []
        }');

        return $server;
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

        $component = new ListenersComponent();
        $component->register($server);

        $server['dispatcher']->emit('redis-connected', array($server, $client, $conn));
        $server['dispatcher']->emit('stomp-connected', array($server, $server['stomp-client']));
        $server['dispatcher']->emit('boot-connected', array($server));
    }
}

class ListenerTester implements JobListenerInterface
{
    public function attach(GloubsterServerInterface $server)
    {
        $server['test-token'] = true;
    }

    public static function create(GloubsterServer $server, array $options)
    {
        $server['created'] = $options;
        return new static();
    }
}


class ListenerFailTester implements JobListenerInterface
{
    public function attach(GloubsterServerInterface $server)
    {
    }

    public static function create(GloubsterServer $server, array $options)
    {
        throw new RuntimeException('fails for test');
    }
}
