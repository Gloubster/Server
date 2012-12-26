<?php

namespace Gloubster\Server\Component;

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
            $listener = $listenerConf['type']::create($server['loop'], $listenerConf['options']);
            $listener->attach($server);
        }
    }
}
