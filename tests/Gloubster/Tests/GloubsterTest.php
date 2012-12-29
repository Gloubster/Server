<?php

namespace Gloubster\Tests;

use Gloubster\Configuration;
use Gloubster\Server\GloubsterServer;

abstract class GloubsterTest extends \PHPUnit_Framework_TestCase
{
    protected function getServer()
    {
        $ws = $this->getMockBuilder('Gloubster\\Server\\WebsocketApplication')
            ->disableOriginalConstructor()
            ->getMock();

        $client = $this->getStompClient();
        $loop = $this->getEventLoop();
        $logger = $this->getLogger();

        $conf = $this->getMockBuilder('Gloubster\\Configuration')
            ->disableOriginalConstructor()
            ->getMock();

        return new GloubsterServer($ws, $client, $loop, $conf, $logger);
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

    protected function getLogger()
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
}
