<?php

namespace Gloubster\Tests\Server;

use Gloubster\Server\SessionHandler;
use Gloubster\Tests\GloubsterTest;

class SessionHandlerTest extends GloubsterTest
{
    public function testFactoryMemcached()
    {
        $this->probeExtension('Memcached');

        $conf = $this->getTestConfiguration();
        $conf['session-server'] = array(
            "type" => "memcached",
            "host" => "localhost",
            "port" => 11211,
        );

        $sessionHandler = SessionHandler::factory($conf);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcachedSessionHandler', $sessionHandler);
    }

    public function testFactoryMemcache()
    {
        $this->probeExtension('Memcache');

        $conf = $this->getTestConfiguration();
        $conf['session-server'] = array(
            "type" => "memcache",
            "host" => "localhost",
            "port" => 11211,
        );

        $sessionHandler = SessionHandler::factory($conf);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcacheSessionHandler', $sessionHandler);
    }

    /**
     * @expectedException Gloubster\Exception\RuntimeException
     */
    public function testFactoryMemcacheWithWrongPort()
    {
        $this->probeExtension('Memcache');

        $conf = $this->getTestConfiguration();
        $conf['session-server'] = array(
            "type" => "memcache",
            "host" => "localhosted",
            "port" => 800,
        );

        SessionHandler::factory($conf);
    }

    /**
     * @expectedException Gloubster\Exception\RuntimeException
     */
    public function testFactoryMemcachedWithWrongPort()
    {
        $this->probeExtension('Memcached');

        $conf = $this->getTestConfiguration();
        $conf['session-server'] = array(
            "type" => "memcached",
            "host" => "localhosted",
            "port" => 800,
        );

        SessionHandler::factory($conf);
    }

    /**
     * @dataProvider getUnsupportedTypes
     * @expectedException Gloubster\Exception\RuntimeException
     */
    public function testFactoryUnsupported($format)
    {
        $conf = $this->getTestConfiguration();
        $conf['session-server'] = array(
            "type" => $format,
            "host" => "localhost",
            "port" => 11211,
        );

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

    private function probeExtension($extension)
    {
        if (!class_exists($extension)) {
            $this->markTestSkipped(sprintf('%s extension not loaded', $extension));
        }
    }
}
