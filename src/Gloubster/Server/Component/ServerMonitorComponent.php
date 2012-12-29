<?php

namespace Gloubster\Server\Component;

use Gloubster\Server\WebsocketApplication;
use Gloubster\Server\GloubsterServer;
use Monolog\Logger;
use Predis\Async\Connection\ConnectionInterface as PredisConnection;
use Predis\Async\Client as PredisClient;
use React\Curry\Util as Curry;
use React\Stomp\Client;

class ServerMonitorComponent implements ComponentInterface
{

    /**
     * {@inheritdoc}
     */
    public function register(GloubsterServer $server)
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
