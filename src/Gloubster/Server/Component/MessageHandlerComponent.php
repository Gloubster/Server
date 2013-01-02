<?php

namespace Gloubster\Server\Component;

use Gloubster\Server\GloubsterServerInterface;
use Gloubster\Server\MessageHandler as MessageHandler;
use React\Stomp\Client;

class MessageHandlerComponent implements ComponentInterface
{
    public function register(GloubsterServerInterface $server)
    {
        $server['message-handler'] = $server->share(function ($server) {
            return new MessageHandler($server['stomp-client'], $server['monolog']);
        });
    }
}
