<?php

namespace Gloubster\Server\Component;

use Gloubster\RabbitMQ\Configuration as RabbitMQConf;
use Gloubster\Server\GloubsterServer;
use Predis\Async\Connection\ConnectionInterface as PredisConnection;
use Predis\Async\Client as PredisClient;
use React\Stomp\Client;

class WorkerMonitorBroadcastComponent implements ComponentInterface
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
        $stomp->subscribe(sprintf('/exchange/%s', RabbitMQConf::EXCHANGE_MONITOR), function (Frame $frame) {
            $server['websocket-application']->onPresence(unserialize($frame->body));
        });
    }

    /**
     * {@inheritdoc}
     */
    public function registerRedis(GloubsterServer $server, PredisClient $client, PredisConnection $conn)
    {
    }
}
