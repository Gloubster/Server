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
        $server['dispatcher']->on('stomp-connected', function (GloubsterServer $server, Client $stomp) {
            $stomp->subscribe(sprintf('/exchange/%s', RabbitMQConf::EXCHANGE_MONITOR), function (Frame $frame) use ($server) {
                $server['websocket-application']->onPresence(unserialize($frame->body));
            });
        });
    }
}
