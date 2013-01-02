<?php

namespace Gloubster\Tests\Server\Component;

use Gloubster\Server\Component\WebsocketServerComponent;
use Gloubster\Tests\GloubsterTest;
use React\EventLoop\Factory as LoopFactory;

class WebsocketServerComponentTest extends GloubsterTest
{
    /** @test */
    public function itShouldRegister()
    {
        $server = $this->getServer();
        $server['loop'] = LoopFactory::create();

        $server['dispatcher']->on('websocket-application-connected', function () use ($server) {
            $server->stop();
        });

        $server->run();
    }

    public function testEvents()
    {
        $server = $this->getServer();

        $component = new WebsocketServerComponent();
        $component->register($server);

        $server['dispatcher']->emit('redis-connected', array($server, $this->getPredisAsyncClient(), $this->getPredisAsyncConnection()));
        $server['dispatcher']->emit('stomp-connected', array($server, $server['stomp-client']));
        $server['dispatcher']->emit('booted', array($server));
    }
}
