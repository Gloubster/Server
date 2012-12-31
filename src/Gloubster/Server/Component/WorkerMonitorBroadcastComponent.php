<?php

namespace Gloubster\Server\Component;

use Gloubster\RabbitMQ\Configuration as RabbitMQConf;
use Gloubster\Server\GloubsterServerInterface;
use React\Stomp\Client;

class WorkerMonitorBroadcastComponent implements ComponentInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(GloubsterServerInterface $server)
    {
        $server['dispatcher']->on('stomp-connected', function (GloubsterServerInterface $server, Client $stomp) {
            $stomp->subscribe(sprintf('/exchange/%s', RabbitMQConf::EXCHANGE_MONITOR), function (Frame $frame) use ($server) {
                $server['websocket-application']->onPresence(unserialize($frame->body));
            });
        });
    }
}
