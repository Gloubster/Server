<?php

namespace Gloubster\Server\Component;

use Gloubster\Server\WebsocketApplication;
use Gloubster\Server\GloubsterServer;
use React\Curry\Util as Curry;
use React\Stomp\Client;

class ServerMonitorComponent implements ComponentInterface
{

    /**
     * {@inheritdoc}
     */
    public function register(GloubsterServer $server)
    {
        $server['loop']->addPeriodicTimer(0.1, Curry::bind(array($this, 'brodcastServerInformations'), $server['websocket-application']));;
    }

    /**
     * {@inheritdoc}
     */
    public function registerSTOMP(GloubsterServer $server, Client $stomp)
    {
    }

    public function brodcastServerInformations(WebsocketApplication $wsApplication)
    {
        $wsApplication->onServerInformation(array(
             'memory' => memory_get_usage(),
        ));
    }
}
