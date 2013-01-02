<?php

namespace Gloubster\Server\Component;

use Gloubster\Server\GloubsterServerInterface;
use Gloubster\Server\MessageHandler as MessageHandler;
use React\Stomp\Client;

class MessageHandlerComponent implements ComponentInterface
{
    public function register(GloubsterServerInterface $server)
    {
        $server['dispatcher']->on('stomp-connected', function (GloubsterServerInterface $server, Client $stomp) {
            $server['message-handler'] = new MessageHandler($stomp, $server['monolog']);
        });
    }
}
