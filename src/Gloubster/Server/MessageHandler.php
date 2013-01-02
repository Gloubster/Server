<?php

namespace Gloubster\Server;

use Gloubster\Exception\RuntimeException;
use Gloubster\Message\Factory as MessageFactory;
use Gloubster\Message\Job\JobInterface;
use Gloubster\RabbitMQ\Configuration as RabbitMQConf;
use Monolog\Logger;
use React\Stomp\Client;

class MessageHandler
{
    private $client;
    private $logger;

    public function __construct(Client $client, Logger $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    public function receive($message)
    {
        $data = null;

        try {
            $data = MessageFactory::fromJson($message);
        } catch (RuntimeException $e) {
            $this->logger->addError(sprintf('Trying to sumbit a non-job message, got error %s with message %s', $e->getMessage(), $message));
            return false;
        }

        if (!$data instanceof JobInterface) {
            $this->logger->addError(sprintf('Trying to sumbit a non-job message : %s', $message));
            return false;
        }

        if (!$this->client->isConnected()) {
            $this->logger->addError(sprintf('STOMP server is not connected'));
            return false;
        }

        $this->client->send(sprintf('/exchange/%s', RabbitMQConf::EXCHANGE_DISPATCHER), $data->toJson());

        return true;
    }

    public function error(\Exception $error)
    {
        $this->logger->addError($error->getMessage());
    }
}
