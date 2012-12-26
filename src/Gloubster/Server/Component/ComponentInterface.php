<?php

namespace Gloubster\Server\Component;

use Gloubster\Server\GloubsterServer;
use React\Stomp\Client;

interface ComponentInterface
{
    /**
     * Register the component in the provided GloubsterServer
     *
     * @param GloubsterServer $server
     */
    public function register(GloubsterServer $server);

    /**
     * Register STOMP services
     *
     * @param GloubsterServer $server
     * @param Client $stomp
     */
    public function registerSTOMP(GloubsterServer $server, Client $stomp);
}
