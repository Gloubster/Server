<?php

namespace Gloubster\Server\Component;

use Predis\Async\Connection\ConnectionInterface as PredisConnection;
use Predis\Async\Client as PredisClient;
use Gloubster\Server\GloubsterServer;
use React\Stomp\Client;

class ListenersComponent implements ComponentInterface
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
        foreach ($server['configuration']['listeners'] as $listenerConf) {
            $listener = $listenerConf['type']::create($server['loop'], $server['monolog'], $listenerConf['options']);
            $listener->attach($server);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function registerRedis(GloubsterServer $server, PredisClient $client, PredisConnection $conn)
    {
    }
}
