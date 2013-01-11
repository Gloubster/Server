<?php

namespace Gloubster\Server\Component;

use Gloubster\Exception\RuntimeException;
use Gloubster\Message\Factory as MessageFactory;
use Gloubster\Configuration as RabbitMQConf;
use Gloubster\Server\GloubsterServerInterface;
use React\Stomp\Client;
use React\Stomp\Protocol\Frame;

class WorkerMonitorBroadcastComponent implements ComponentInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(GloubsterServerInterface $server)
    {
        $server['dispatcher']->on('stomp-connected', function (GloubsterServerInterface $server, Client $stomp) {
            $stomp->subscribe(sprintf('/exchange/%s', RabbitMQConf::EXCHANGE_MONITOR), function (Frame $frame) use ($server) {

                try {
                    $data = MessageFactory::fromJson($frame->body);

                    $server['websocket-application']->onPresence($data);
                } catch (RuntimeException $e) {
                    $server['monolog']->addError(sprintf('Receiving wrong monitor message : %s', $frame->body));
                }
            });
        });
    }
}
