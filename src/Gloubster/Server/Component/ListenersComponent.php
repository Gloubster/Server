<?php

namespace Gloubster\Server\Component;

use Gloubster\Server\GloubsterServer;
use Gloubster\Exception\RuntimeException;
use Gloubster\Server\Listener\JobListenerInterface;
use Predis\Async\Connection\ConnectionInterface as PredisConnection;
use Predis\Async\Client as PredisClient;
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
        $server['monolog']->addInfo(sprintf('Going to attach %d listeners', count($server['configuration']['listeners'])));

        foreach ($server['configuration']['listeners'] as $listenerConf) {
            $class_name = $listenerConf['type'];

            if (!class_exists($class_name)) {
                $server['monolog']->addError(sprintf('%s is not a valid classname', $class_name));
                continue;
            }

            if (!in_array('Gloubster\Server\Listener\JobListenerInterface', class_implements($class_name))) {
                $server['monolog']->addError(sprintf('%s is not implementing JobListenerInterface', $class_name));
                continue;
            }

            try {
                $listener = $class_name::create($server['loop'], $server['monolog'], $listenerConf['options']);
            } catch (RuntimeException $e) {
                $server['monolog']->addError(sprintf('Error while creating listener %s : %s', $listenerConf['type'], $e->getMessage()));
                continue;
            }

            $server['monolog']->addInfo(sprintf('Attaching listener %s', get_class($listener)));

            $listener->attach($server);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function registerRedis(GloubsterServer $server, PredisClient $client, PredisConnection $conn)
    {
    }
}
