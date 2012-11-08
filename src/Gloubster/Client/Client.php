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

        if( $configuration['client']['stack']) {
            $this->jobsContainer->setCapacity( $configuration['client']['stack']);
        }

    }

    public function run()
    {
        while (true) {
//            try {
            $this->logger->addInfo('about to drain');
            $this->jobsContainer->drain();
            $this->logger->addInfo('about to fill');
            $this->jobsContainer->fill();
            if(count($this->jobsContainer) === 0) {
                //$this->logger->addInfo('about to Stopping');
                //break;
            }
            $this->logger->addInfo(sprintf('Now using %dMo', memory_get_usage() >> 20));
            usleep($this->period);
//            } catch(\Exception $e) {
//                $this->logger->addCritical('CRITICAL EXCEPTION'.$e->getFile().':'.$e->getLine().' - '.$e->getMessage());
//            }
        }
    }
}
