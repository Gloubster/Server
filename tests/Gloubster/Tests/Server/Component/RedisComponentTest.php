<?php

namespace Gloubster\Tests\Server\Component;

use Gloubster\Server\Component\RedisComponent;
use Gloubster\Tests\GloubsterTest;
use React\EventLoop\Factory as LoopFactory;

/**
 * @covers Gloubster\Server\Component\RedisComponent
 */
class RedisComponentTest extends GloubsterTest
{
    /** @test */
    public function itShouldRegister()
    {
        $server = $this->getServer();
        $server['configuration'] = $this->getTestConfiguration();

        $server['loop'] = LoopFactory::create();

        $server['dispatcher']->on('redis-connected', function () use ($server) {
            $server->stop();
        });

        $server->run();
    }

    public function testEvents()
    {
        $server = $this->getServer();

        $component = new RedisComponent();
        $component->register($server);

        $server['dispatcher']->emit('redis-connected', array($server, $this->getPredisAsyncClient(), $this->getPredisAsyncConnection()));
        $server['dispatcher']->emit('stomp-connected', array($server, $server['stomp-client']));
        $server['dispatcher']->emit('booted', array($server));
    }
}
