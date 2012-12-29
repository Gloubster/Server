<?php

namespace Gloubster\Server\Component;

use Gloubster\RabbitMQ\Configuration as RabbitMQConf;
use Gloubster\Server\GloubsterServer;
use Gloubster\Message\Factory;
use Gloubster\Documents\Job;
use Gloubster\Documents\Garbage;
use Doctrine\ODM\MongoDB\DocumentManager;
use Gloubster\Exception\RuntimeException;
use Gloubster\Message\Job\JobInterface;
use Monolog\Logger;
use Predis\Async\Connection\ConnectionInterface as PredisConnection;
use Predis\Async\Client as PredisClient;
use React\Promise\Deferred;
use React\Stomp\AckResolver;
use React\Stomp\Client;
use React\Stomp\Protocol\Frame;
use React\Curry\Util as Curry;


class LogBuilderComponent implements ComponentInterface
{

    /**
     * {@inheritdoc}
     */
    public function register(GloubsterServer $server)
    {
        $server['dispatcher']->on('redis-connected', function (GloubsterServer $server, PredisClient $client, PredisConnection $conn) {
            $server['stomp-client']->subscribeWithAck(
                sprintf('/queue/%s', RabbitMQConf::QUEUE_LOGS), 'client',
                Curry::bind(array($this, 'handleLog'), $client, $server['monolog'])
            );
        });

    }

    public function handleLog(PredisClient $redis, Logger $logger, Frame $frame, AckResolver $resolver)
    {
        $logger->addInfo(sprintf('Processing job %s', $frame->getHeader('delivery_tag')));

        $error = false;

        try {
            $job = Factory::fromJson($frame->body);
            if (!$job instanceof JobInterface) {
                $error = true;
            }
        } catch (RuntimeException $e) {
            $error = true;
        }

        if (!$error) {
            $promise = $this->saveJob($redis, $job);
        } else {
            $promise = $this->saveGarbage($redis, $frame->body);
        }

        $promise->then(function() use ($resolver) {
            $resolver->ack();
        });

        return $promise;
    }

    private function saveJob(PredisClient $redis, JobInterface $job)
    {
        $deferred = new Deferred();

        $tx = $redis->multiExec();

        $tx->incr('job-counter');
        $tx->execute(function ($replies, $redis) use ($deferred, $job) {
            $hashId = 'job-' . $replies[0];

            $hash = array_merge(array($hashId), $this->hashJob($job), array(function() use ($deferred, $hashId){
                $deferred->resolve($hashId);
            }));

            call_user_func_array(array($redis, 'hmset'), $hash);
        });

        return $deferred->promise()
          ->then(function ($hashId) use ($redis) {
                $saddDeferred = new Deferred();

                $redis->sadd('jobs', $hashId, function() use ($hashId, $saddDeferred) {
                    $saddDeferred->resolve($hashId);
                });

                return $saddDeferred->promise();
            });
    }

    private function saveGarbage(PredisClient $redis, $data)
    {
        $deferred = new Deferred();

        $tx = $redis->multiExec();

        $tx->incr('garbage-counter');
        $tx->execute(function ($replies, $redis) use ($deferred, $data) {
            $hashId = 'garbage-' . $replies[0];

            $redis->set($hashId, $data, function() use ($deferred, $hashId){
                $deferred->resolve($hashId);
            });
        });

        return $deferred->promise()
          ->then(function ($hashId) use ($redis) {
                $saddDeferred = new Deferred();

                $redis->sadd('garbages', $hashId, function() use ($hashId, $saddDeferred) {
                    $saddDeferred->resolve($hashId);
                });

                return $saddDeferred->promise();
            });
    }

    private function hashJob(JobInterface $job)
    {
        $hash = array();

        foreach(json_decode($job->toJson(), true) as $key => $value)
        {
            if(is_array($value)) {
                $value = json_encode($value);
            }
            $hash[] = $key;
            $hash[] = $value;
        }

        return $hash;
    }
}
