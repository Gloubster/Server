<?php

namespace Gloubster\Server\Component;

use Gloubster\Server\GloubsterServerInterface;
use Predis\Async\Client as PredisClient;
use Predis\Async\Connection\ConnectionInterface as PredisConnection;

class RedisComponent implements ComponentInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(GloubsterServerInterface $server)
    {
        $server['redis-client.started'] = false;


        $server['dispatcher']->on('start', function ($server) {

            $redisErrorHandler = function (PredisClient $client, \Exception $e, PredisConnection $conn) use ($server) {
                call_user_func(array($server, 'logError'), $e);
            };

            $redisOptions = array(
                'on_error'  => $redisErrorHandler,
                'eventloop' => $server['loop'],
            );

            $server['redis-client'] = new PredisClient(sprintf('tcp://%s:%s', $server['configuration']['redis-server']['host'], $server['configuration']['redis-server']['port']), $redisOptions);
            $server['redis-client']->connect(function ($client, $conn) use ($server) {
                $server['monolog']->addInfo('Connected to Redis Server !');
                $server['dispatcher']->emit('redis-connected', array($this, $client, $conn));
                $server['redis-client.started'] = true;
                $server->probeAllSystems();
            });
            $server['monolog']->addInfo('Connecting to Redis server...');
        });

        $server['dispatcher']->on('stop', function ($server) {
            $server['redis-client']->disconnect();
            $server['redis-client.started'];
            $server['monolog']->addInfo('Redis Server shutdown');
        });
    }
}
