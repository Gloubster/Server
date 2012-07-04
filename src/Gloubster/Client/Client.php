<?php

namespace Gloubster\Client;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Gloubster\Communication\Query;
use Monolog\Logger;

class Client
{
    const PRIORITY_HIGH = 'high';
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';

    protected $client;

    /**
     *
     * @var Logger
     */
    protected $logger;
    protected $jobs;
    protected $dm;
    protected $configuration;
    protected $delivery;

    public function __construct(\GearmanClient $client, \Gloubster\Client\Configuration $configuration, DocumentManager $DM, Logger $logger)
    {
        $this->configuration = $configuration;
        $this->client = $client;
        $this->logger = $logger;
        $this->dm = $DM;
        $this->jobs = new ArrayCollection();

        $this->delivery = \Gloubster\Delivery\Factory::build($configuration['delivery']['name'], $configuration['delivery']['configuration']);
    }

    public function addServer($server, $port)
    {
        $this->client->addServer($server, $port);
    }

    public function run()
    {
        while (true) {
            $this->addJobs(max($this->configuration['client']['stack'] - count($this->jobs), 0));
            $this->watchJobs();
            $this->logger->addInfo(sprintf('Now using %dMo', memory_get_usage() >> 20));
            usleep($this->configuration['client']['period'] * 1000);
        }
    }

    private function watchJobs()
    {
        foreach ($this->jobs as $uuid => $jobHandle) {
            $stat = $this->client->jobStatus($jobHandle);

            if ( ! $stat[0]) {
                $this->jobs->remove($uuid);
                $this->logger->addInfo(sprintf('Job %s done', $uuid));
            } else {
                if ($stat[1]) {
                    if ($stat[3] > 0) {
                        $this->logger->addInfo(sprintf('Job %s running ; %f', $uuid, $stat[2] / $stat[3]));
                    } else {
                        $this->logger->addInfo(sprintf('Job %s running', $uuid));
                    }
                } else {
                    $this->logger->addInfo(sprintf('Job %s pending', $uuid));
                }
            }
        }
    }

    private function addJobs($quantity = 10)
    {
        $this->logger->addInfo(sprintf('Looking for %d jobsets', $quantity));

        if ($quantity < 1) {
            return;
        }

        $cursor = $this->dm->createQueryBuilder('Gloubster\\Documents\\JobSet')
            ->limit($quantity)
            ->getQuery()
            ->execute();

        /* @var $cursor \Doctrine\ODM\MongoDB\EagerCursor */
        foreach ($cursor as $jobset) {
            foreach ($jobset->getSpecifications() as $specification) {
                $parameters = $this->parametersToArray($specification->getParameters());

                $query = new Query($specification->getId(), $jobset->getFile(), $this->delivery->getName(), $this->delivery->getSignature(), $parameters);

                $this->addJob($this->getJobName($specification->getName()), $query);
            }
        }
    }

    private function parametersToArray(\Doctrine\ODM\MongoDB\PersistentCollection $parameters)
    {
        $ret = array();

        foreach ($parameters as $parameter) {
            $ret[$parameter->getName()] = $parameter->getValue();
        }

        return $ret;
    }

    protected function getJobName($specName)
    {
        switch ($specName) {
            case 'image':
                return Query::FUNCTION_TRANSMUTE_IMAGE;
                break;
        }
    }

    private function addJob($function, Query $query, $priority = self::PRIORITY_NORMAL)
    {
        switch ($priority) {
            case self::PRIORITY_LOW:
                $doPriority = 'doLowBackground';
                break;
            case self::PRIORITY_HIGH:
                $doPriority = 'doHighBackground';
                break;
            default:
            case self::PRIORITY_NORMAL:
                $doPriority = 'doBackground';
                break;
        }

        $jobHandle = $this->client->$doPriority($function, serialize($query), $query->getUuid());

        switch ($this->client->returnCode()) {
            case GEARMAN_SUCCESS:
                $this->logger->addInfo(
                    sprintf(
                        'Sending job `%s` with payload %s', $function, serialize($query)
                    )
                );
                $this->jobs->set($query->getUuid(), $jobHandle);
                break;
            case GEARMAN_NO_SERVERS:
                $this->logger->addError('No servers, please add server before submitting a job');
                break;
            case GEARMAN_LOST_CONNECTION:
                $this->logger->addError('Connection lost during the request');
                break;
            default:
                $this->logger->addError(
                    sprintf(
                        'Gearman client error : %s', $this->client->error()
                    )
                );
                break;
        }
    }

    /**
     * Ping all gearman servers and return true if all of them are online.
     * Return false if at least one is offline.
     *
     * @return Boolean
     */
    public function ping()
    {
        return @$this->client->ping('Hello There');
    }
}
