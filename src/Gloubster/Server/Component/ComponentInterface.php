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

    /**
     * Register STOMP services
     *
     * @param GloubsterServer $server
     * @param Client $stomp
     */
    public function registerSTOMP(GloubsterServer $server, Client $stomp);

    /**
     * Register Redis services
     *
     * @param GloubsterServer  $server
     * @param PredisClient     $client
     * @param PredisConnection $conn
     */
    public function registerRedis(GloubsterServer $server, PredisClient $client, PredisConnection $conn);

    /**
     * This method is triggered once all server properties are boot
     *
     * @param GloubsterServer  $server
     */
    public function boot(GloubsterServer $server);
}
