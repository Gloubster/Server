<?php

namespace Gloubster\Tests;

use Gloubster\Server\GloubsterServer;

abstract class GloubsterTest extends \PHPUnit_Framework_TestCase
{
    protected function getServer()
    {
        $ws = $this->getMockBuilder('Gloubster\\Server\\WebsocketApplication')
            ->disableOriginalConstructor()
            ->getMock();

        $client = $this->getMockBuilder('React\\Stomp\\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $loop = $this->getMockBuilder('React\\EventLoop\\LoopInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $conf = $this->getMockBuilder('Gloubster\\Configuration')
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $this->getMockBuilder('Monolog\\Logger')
            ->disableOriginalConstructor()
            ->getMock();

        return new GloubsterServer($ws, $client, $loop, $conf, $logger);
    }
}
