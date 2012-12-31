<?php

namespace Gloubster\Tests\Server\Component;

use Gloubster\Server\Component\STOMPComponent;
use Gloubster\Tests\GloubsterTest;
use React\EventLoop\Factory as LoopFactory;

/**
 * @covers Gloubster\Server\Component\STOMPComponent
 */
class STOMPComponentTest extends GloubsterTest
{
    /** @test */
    public function itShouldRegister()
    {
        $server = $this->getServer();
        $server['loop'] = LoopFactory::create();
        $server->register(new STOMPComponent());

        $server['dispatcher']->on('stomp-connected', function () use ($server) {
            $server->stop();
        });

        $server['loop']->addTimer(15, function () use ($server) {
            $server->stop();
        });

        $server->run();
    }

    public function testEvents()
    {
        $server = $this->getServer();

        $component = new STOMPComponent();
        $component->register($server);

        $server['dispatcher']->emit('redis-connected', array($server, $this->getPredisAsyncClient(), $this->getPredisAsyncConnection()));
        $server['dispatcher']->emit('stomp-connected', array($server, $server['stomp-client']));
        $server['dispatcher']->emit('booted', array($server));
    }
}
