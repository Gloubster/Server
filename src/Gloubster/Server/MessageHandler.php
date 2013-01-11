<?php

namespace Gloubster\Server;

use Gloubster\Exception\RuntimeException;
use Gloubster\Message\Job\ImageJob;
use Gloubster\Message\Job\VideoJob;
use Gloubster\Message\Factory as MessageFactory;
use Gloubster\Message\Job\JobInterface;
use Gloubster\Configuration as RabbitMQConf;
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
            throw new RuntimeException(sprintf('Trying to sumbit a non-job message, '
                . 'got error %s with message %s', $e->getMessage(), $message), $e->getCode(), $e);
        }

        if (!$data instanceof JobInterface) {
            throw new RuntimeException(sprintf('Trying to sumbit a non-job message : %s', $message));
        }

        if (!$this->client->isConnected()) {
            throw new RuntimeException(sprintf('STOMP server is not connected'));
        }

        try {
            $data->isOk(true);
        } catch (RuntimeException $e) {
            throw new RuntimeException(sprintf('Submit an invalid message job : %s', $e->getMessage()));
        }

        switch (true) {
            case $data instanceof ImageJob:
                $routingKey = RabbitMQConf::ROUTINGKEY_IMAGE_PROCESSING;
                break;
            case $data instanceof VideoJob:
                $routingKey = RabbitMQConf::ROUTINGKEY_VIDEO_PROCESSING;
                break;
            default:
                $routingKey = RabbitMQConf::ROUTINGKEY_ERROR;
                break;
        }

        $this->client->send(sprintf('/exchange/%s/%s', RabbitMQConf::EXCHANGE_DISPATCHER, $routingKey), $data->toJson());
    }

    public function error(\Exception $error)
    {
        $this->logger->addError($error->getMessage());
    }
}
