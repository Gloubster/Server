<?php

namespace Gloubster\Tests\Server;

use Gloubster\Server\SessionHandler;
use Gloubster\Configuration;

class SessionHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryMemcached()
    {
        $conf = new Configuration('
    {
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
            "type": "memcached",
            "host": "localhost",
            "port": 11211
        }
    }
');

        $sessionHandler = SessionHandler::factory($conf);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcachedSessionHandler', $sessionHandler);
    }

    public function testFactoryMemcache()
    {
        $conf = new Configuration('
    {
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
        }
    }
');

        $sessionHandler = SessionHandler::factory($conf);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcacheSessionHandler', $sessionHandler);
    }

    /**
     * @expectedException Gloubster\Exception\RuntimeException
     */
    public function testFactoryMemcacheWithWrongPort()
    {
        $conf = new Configuration('
    {
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
            "host": "localhosted",
            "port": 800
        }
    }
');

        $sessionHandler = SessionHandler::factory($conf);
    }

    /**
     * @expectedException Gloubster\Exception\RuntimeException
     */
    public function testFactoryMemcachedWithWrongPort()
    {
        $conf = new Configuration('
    {
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
            "type": "memcached",
            "host": "localhosted",
            "port": 800
        }
    }
');

        $sessionHandler = SessionHandler::factory($conf);
    }

    /**
     * @dataProvider getUnsupportedTypes
     * @expectedException Gloubster\Exception\RuntimeException
     */
    public function testFactoryUnsupported($format)
    {
        $conf = new Configuration('
    {
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
            "type": "'.$format.'",
            "host": "localhost",
            "port": 11211
        }
    }
');

        SessionHandler::factory($conf);
    }

    public function getUnsupportedTypes()
    {
        return array(
            array('mongo'),
            array('pdo'),
            array('unknown'),
        );
    }
}
