<?php

namespace Gloubster\Server\Console;

use Gloubster\Server\Console\AbstractCommand;
use Gloubster\Server\Worker\LogWorker;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPChannelException;
use PhpAmqpLib\Connection\AMQPConnection;
use Gloubster\Configuration;
use Gloubster\Exchange;
use Gloubster\Queue;
use Gloubster\RoutingKey;
use Gloubster\Worker\Factory;
use Monolog\Logger;
use Neutron\TemporaryFilesystem\TemporaryFilesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class QueuesEnsure extends AbstractCommand
{
    private $admin;
    private $logger;
    private $channel;
    private $connection;
    private $conf;

    public function __construct(AMQPConnection $conn, Configuration $conf)
    {
        parent::__construct('queues:ensure-declaration');

        $this->connection = $conn;
        $this->channel = $this->connection->channel();
        $this->conf = $conf;
        $this->setDescription('Ensure that queues and echanges are declared and bounds');

        return $this;
    }

    public function doExecute(InputInterface $input, OutputInterface $output)
    {
        $attributes = array(
            'x-dead-letter-routing-key' => array('S', RoutingKey::ERROR),
            'x-dead-letter-exchange' => array('S', Exchange::GLOUBSTER_DISPATCHER),
        );

        $this->ensureQueue(Queue::IMAGE_PROCESSING, $attributes);
        $this->ensureQueue(Queue::VIDEO_PROCESSING, $attributes);
        $this->ensureQueue(Queue::LOGS);
        $this->ensureQueue(Queue::ERRORS);

        $this->ensureExchange(Exchange::GLOUBSTER_DISPATCHER, 'direct');

        $this->channel->queue_bind(Queue::ERRORS, Exchange::GLOUBSTER_DISPATCHER, RoutingKey::ERROR);
        $this->channel->queue_bind(Queue::IMAGE_PROCESSING, Exchange::GLOUBSTER_DISPATCHER, RoutingKey::IMAGE_PROCESSING);
        $this->channel->queue_bind(Queue::VIDEO_PROCESSING, Exchange::GLOUBSTER_DISPATCHER, RoutingKey::VIDEO_PROCESSING);
    }

    private function ensureQueue($name, $arguments = array())
    {
        $this->container['monolog']->addInfo(sprintf('Ensure queue %s ...', $name));

        if (!$this->hasQueue($name)) {
            $this->container['monolog']->addInfo(sprintf('Queue %s does not exist, creating it', $name));
            $this->createQueue($name, $arguments);
        } elseif (!$this->hasQueue($name)) {
            $this->container['monolog']->addInfo(sprintf('Queue %s does not exist with right settings, re-creating it', $name));
            $this->deleteQueue($name, $arguments);
            $this->createQueue($name, $arguments);
        } else {
            $this->container['monolog']->addInfo(sprintf('Queue %s OK', $name));
        }

        return;
    }

    private function ensureExchange($name, $type)
    {
        $this->container['monolog']->addInfo(sprintf('Declaring exchange %s (%s) ...', $name, $type));

        if (!$this->hasExchange($name, $type)) {
            $this->container['monolog']->addInfo(sprintf('Exchange %s (%s) do not seem to exist, creating it', $name, $type));
            $this->createExchange($name, $type);
        } else {
            $this->container['monolog']->addInfo(sprintf('Exchange %s (%s) OK', $name, $type));
        }

        return;
    }

    private function hasQueue($name)
    {
        $channel = $this->connection->channel();

        $ret = true;

        try {
            $channel->queue_declare($name, true, true);
            $channel->close();
        } catch (AMQPChannelException $e) {
            if ($e->getCode() == '404') {
                $ret = false;
            } else {
                throw new \Exception(sprintf('Unexpected code %s', $e->getCode()));
            }
        }

        unset($channel);

        return $ret;
    }

    private function hasExchange($name, $type)
    {
        $channel = $this->connection->channel();

        $ret = true;

        try {
            $channel->exchange_declare($name, $type, true, true);
            $channel->close();
        } catch (AMQPChannelException $e) {
            if ($e->getCode() == '404') {
                $ret = false;
            } else {
                throw new \Exception(sprintf('Unexpected code %s', $e->getCode()));
            }
        }

        unset($channel);

        return $ret;
    }

    private function queueHasSettings($name, $arguments)
    {
        $channel = $this->connection->channel();

        $ret = true;

        try {
            $channel->queue_declare($name, false, true, false, false, false, $arguments);
            $channel->close();
        } catch (AMQPChannelException $e) {
            if ($e->getCode() == '406') {
                $ret = false;
            } else {
                throw new \Exception(sprintf('Unexpected code %s', $e->getCode()));
            }
        }

        unset($channel);

        return $ret;
    }

    private function createQueue($name, array $arguments)
    {
        $this->channel->queue_declare($name, false, true, false, false, false, $arguments);

        return $this;
    }

    private function createExchange($name, $type)
    {
        $this->channel->exchange_declare($name, $type, false, true);

        return $this;
    }

    private function deleteQueue($name)
    {
        $this->channel->queue_delete($name);

        return $this;
    }

    private function purgeQueue($name)
    {
        $this->channel->queue_purge($name);
    }
}