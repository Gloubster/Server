<?php

namespace Gloubster\Client;

use Doctrine\ODM\MongoDB\DocumentManager;
use Monolog\Logger;

class Client
{
    /**
     *
     * @var Logger
     */
    protected $logger;
    protected $period;
    protected $jobsContainer;

    public function __construct(\GearmanClient $client, Configuration $configuration, DocumentManager $DM, Logger $logger)
    {
        $this->logger = $logger;
        $this->period = $configuration['client']['period'] * 1000;

        foreach($configuration['gearman-servers'] as $server) {
            $client->addServer($server['host'], $server['port']);
        }

        $this->jobsContainer = new JobsContainer( $client, $configuration, $DM, $logger);
    }

    public function run()
    {
        while (true) {
            $this->jobsContainer->drain();
            $this->jobsContainer->fill();
            $this->logger->addInfo(sprintf('Now using %dMo', memory_get_usage() >> 20));
            usleep($this->period);
        }
    }
}
