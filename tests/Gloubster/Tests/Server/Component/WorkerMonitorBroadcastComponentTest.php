<?php

namespace Gloubster\Tests\Server\Component;

use Gloubster\Server\Component\WorkerMonitorBroadcastComponent;
use Gloubster\Tests\GloubsterTest;

/**
 * @covers Gloubster\Server\Component\WorkerMonitorBroadcastComponent
 */
class WorkerMonitorBroadcastComponentTest extends GloubsterTest
{
    /** @test */
    public function itShouldRegister()
    {
        $server = $this->getServer();
        $server->register(new WorkerMonitorBroadcastComponent());

        $server['dispatcher']->emit('stomp-connected', array($server, $server['stomp-client']));
    }

    public function testEvents()
    {
        $server = $this->getServer();

        $component = new WorkerMonitorBroadcastComponent();
        $component->register($server);

        $server['dispatcher']->emit('redis-connected', array($server, $this->getPredisAsyncClient(), $this->getPredisAsyncConnection()));
        $server['dispatcher']->emit('stomp-connected', array($server, $server['stomp-client']));
        $server['dispatcher']->emit('booted', array($server));
    }
}
