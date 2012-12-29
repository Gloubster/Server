<?php

namespace Gloubster\Tests\Server\Component;

use Gloubster\Server\Component\RabbitMQMonitorComponent;
use Gloubster\Tests\GloubsterTest;

/**
 * @covers Gloubster\Server\Component\RabbitMQMonitorComponent
 */
class RabbitMQMonitorComponentTest extends GloubsterTest
{
    /** @test */
    public function itShouldRegister()
    {
        $server = $this->getServer();

        $server['configuration'] = $this->getTestConfiguration();
        $server['loop']->expects($this->once())
            ->method('addPeriodicTimer')
            ->with($this->greaterThan(0), $this->anything());

        $component = new RabbitMQMonitorComponent();
        $server->register($component);

        $component->fetchMQInformations($server['websocket-application'], $server['configuration']);
    }

    public function testEvents()
    {
        $server = $this->getServer();
        $server['configuration'] = $this->getTestConfiguration();

        $component = new RabbitMQMonitorComponent();
        $component->register($server);

        $server['dispatcher']->emit('redis-connected', array($server, $this->getPredisAsyncClient(), $this->getPredisAsyncConnection()));
        $server['dispatcher']->emit('stomp-connected', array($server, $server['stomp-client']));
        $server['dispatcher']->emit('boot-connected', array($server));
    }
}
