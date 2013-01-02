<?php

namespace Gloubster\Tests;

use Gloubster\Configuration;
use Gloubster\Server\GloubsterServer;

abstract class GloubsterTest extends \PHPUnit_Framework_TestCase
{
    protected function getServer()
    {
        $loop = $this->getEventLoop();
        $logger = $this->getLogger();

        $conf = $this->getTestConfiguration();

        return new GloubsterServer($loop, $conf, $logger);
    }

    protected function getGloubsterServerMock()
    {
        return $this->getMockBuilder('Gloubster\\Server\\GloubsterServerInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getTestConfiguration()
    {
        return new Configuration(file_get_contents(__DIR__ . '/../../resources/config.json'));
    }

    protected function getPredisAsyncClient()
    {
        return $this->getMockBuilder('Predis\\Async\\Client')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getPredisAsyncConnection()
    {
        return $this->getMockBuilder('Predis\Async\Connection\ConnectionInterface')
                ->disableOriginalConstructor()
                ->getMock();
    }

    protected function getStompClient()
    {
        return $this->getMockBuilder('React\\Stomp\\Client')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function getLogger()
    {
        return $this->getMockBuilder('Monolog\\Logger')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getEventLoop()
    {
        return $this->getMockBuilder('React\\EventLoop\\LoopInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getSessionServer(Configuration $conf)
    {
        switch ($conf['session-server']['type']) {
            case 'memcache':
                if (class_exists('Memcache')) {
                    return;
                }
                break;
            case 'memcached':
                if (class_exists('Memcached')) {
                    return;
                }
                break;
        }

        if (class_exists('Memcache')) {
            $conf['session-server']['type'] = 'memcache';
            return;
        }

        if (class_exists('Memcached')) {
            $conf['session-server']['type'] = 'memcached';
            return;
        }

        $this->markTestSkipped('Neither memcache or memcached extension are present, unable to use the SessionHandler');
    }

    protected function getMessageHandlerMock()
    {
        return $this->getMockBuilder('Gloubster\\Server\\MessageHandler')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
