<?php

namespace Gloubster\Server\Component;

use Predis\Async\Connection\ConnectionInterface as PredisConnection;
use Predis\Async\Client as PredisClient;
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
}
