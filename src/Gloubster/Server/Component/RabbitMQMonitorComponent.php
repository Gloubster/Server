<?php

namespace Gloubster\Server\Component;

use Gloubster\Configuration;
use Gloubster\Server\WebsocketApplication;
use Gloubster\RabbitMQ\Configuration as RabbitMQConf;
use Gloubster\Server\GloubsterServer;
use RabbitMQ\Management\AsyncAPIClient;
use React\Curry\Util as Curry;
use React\Stomp\Client;

class RabbitMQMonitorComponent implements ComponentInterface
{
    private $apiClient;

    private $exchanges = array(
        RabbitMQConf::EXCHANGE_DISPATCHER => null,
        RabbitMQConf::EXCHANGE_MONITOR    => null,
    );

    private $queues = array(
        RabbitMQConf::QUEUE_IMAGE_PROCESSING => null,
        RabbitMQConf::QUEUE_ERRORS           => null,
        RabbitMQConf::QUEUE_LOGS             => null,
        RabbitMQConf::QUEUE_VIDEO_PROCESSING => null,
    );

    /**
     * {@inheritdoc}
     */
    public function register(GloubsterServer $server)
    {
        $this->apiClient = AsyncAPIClient::factory($server['loop'], array_merge($server['configuration']['server'], $server['configuration']['server']['server-management']));
        $server['loop']->addPeriodicTimer(5, Curry::bind(array($this, 'fetchMQInformations'), $server['websocket-application'], $server['configuration']));
    }

    /**
     * {@inheritdoc}
     */
    public function registerSTOMP(GloubsterServer $server, Client $stomp)
    {
    }

    public function fetchMQInformations(WebsocketApplication $wsApplication, Configuration $configuration)
    {
        foreach ($this->queues as $name => $queue) {

            if ($queue === null) {
                $this->queues[$name] = new \RabbitMQ\Management\Entity\Queue();
            }

            $this->apiClient->getQueue($configuration['server']['vhost'], $name, $this->queues[$name])
                ->then(function($queue) use ($name, $wsApplication) {
                    $wsApplication->broadcastQueueInformation($queue);
                });
        }
        foreach ($this->exchanges as $name => $exchange) {

            if ($exchange === null) {
                $this->exchanges[$name] = new \RabbitMQ\Management\Entity\Exchange();
            }

            $this->apiClient->getExchange($configuration['server']['vhost'], $name, $this->exchanges[$name])
                ->then(function($exchange) use ($name, $wsApplication) {
                    $wsApplication->broadcastExchangeInformation($exchange);
                });
        }

        gc_collect_cycles();
        echo "at " . time() . " memory is using " . memory_get_usage() . "\n";
    }
}
