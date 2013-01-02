<?php

namespace Gloubster\Tests\Server\Component;

use Gloubster\Tests\GloubsterTest;
use Gloubster\Server\Component\MessageHandlerComponent;
use React\EventLoop\Factory as LoopFactory;

class MessageHandlerTest extends GloubsterTest
{
    /** @test */
    public function itShouldRegister()
    {
        $server = $this->getServer();
        $server['loop'] = LoopFactory::create();

        $server['dispatcher']->on('stomp-connected', function () use ($server) {
            $server->stop();
        });

        $server->run();
    }

    public function testEvents()
    {
        $server = $this->getServer();

        $component = new MessageHandlerComponent();
        $component->register($server);

        $server['dispatcher']->emit('redis-connected', array($server, $this->getPredisAsyncClient(), $this->getPredisAsyncConnection()));
        $server['dispatcher']->emit('stomp-connected', array($server, $server['stomp-client']));
        $server['dispatcher']->emit('booted', array($server));
    }
}
