<?php

namespace Gloubster\Server\Component;

use Gloubster\Server\GloubsterServer;
use Predis\Async\Client as PredisAsync;
use Predis\Async\Connection\ConnectionInterface;
use React\Stomp\Client;

class StopComponent implements ComponentInterface
{

    /**
     * {@inheritdoc}
     */
    public function register(GloubsterServer $server)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function registerSTOMP(GloubsterServer $server, Client $stomp)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function registerRedis(GloubsterServer $server, PredisAsync $client, ConnectionInterface $conn)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function boot(GloubsterServer $server)
    {
        $server['monolog']->addInfo('StopComponent is now stopping the server, shutting down...');
        $server['loop']->stop();
    }
}
