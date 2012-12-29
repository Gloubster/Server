<?php

namespace Gloubster\Tests\Server\Component;

use Gloubster\Server\Component\ServerMonitorComponent;
use Gloubster\Tests\GloubsterTest;

class ServerMonitorComponentTest extends GloubsterTest
{
    /** @test */
    public function itShouldRegister()
    {
        $server = $this->getServer();

        $server['loop']->expects($this->exactly(2))
            ->method('addPeriodicTimer')
            ->with($this->greaterThan(0), $this->anything());

        $component = new ServerMonitorComponent();
        $server->register($component);

        $component->brodcastServerInformations($server['websocket-application']);

        $server['monolog']->expects($this->once())
            ->method('addDebug');

        $component->displayServerMemory($server['monolog']);
    }

    public function testEvents()
    {
        $server = $this->getServer();

        $component = new ServerMonitorComponent();
        $component->register($server);

        $server['dispatcher']->emit('redis-connected', array($server, $this->getPredisAsyncClient(), $this->getPredisAsyncConnection()));
        $server['dispatcher']->emit('stomp-connected', array($server, $server['stomp-client']));
        $server['dispatcher']->emit('boot-connected', array($server));
    }
}
