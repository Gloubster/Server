<?php

namespace Gloubster\Server\Component;

use Gloubster\Server\WebsocketApplication;
use Gloubster\Server\GloubsterServerInterface;
use Monolog\Logger;
use React\Curry\Util as Curry;

class ServerMonitorComponent implements ComponentInterface
{

    /**
     * {@inheritdoc}
     */
    public function register(GloubsterServerInterface $server)
    {
        $server['loop']->addPeriodicTimer(0.1, Curry::bind(array($this, 'brodcastServerInformations'), $server['websocket-application']));
        $server['loop']->addPeriodicTimer(5, Curry::bind(array($this, 'displayServerMemory'), $server['monolog']));
    }

    public function displayServerMemory(Logger $logger)
    {
        $logger->addDebug(sprintf("Memory is using %d", memory_get_usage()));
    }

    public function brodcastServerInformations(WebsocketApplication $wsApplication)
    {
        $wsApplication->onServerInformation(array(
             'memory' => memory_get_usage(),
        ));
    }
}
