<?php

namespace Gloubster\Client;

use Gloubster\Delivery\DeliveryInterface;
use Gloubster\Delivery\Factory;
use Gloubster\Documents\Specification;
use Gloubster\Communication\Query;
use Gloubster\Communication\Result;
use Gloubster\Exception\InvalidArgumentException;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\PersistentCollection;
use Monolog\Logger;

class JobsContainer implements \Countable
{
    const PRIORITY_HIGH = 'high';
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';

    protected $DM;
    protected $client;
    protected $logger;

    /**
     *
     * @var DeliveryInterface
     */
    protected $delivery;
    protected $stack;
    protected $ratio = 0.5;
    protected $capacity = 500;

    public function __construct(\GearmanClient $client, Configuration $configuration, DocumentManager $DM, Logger $logger)
    {
        $this->logger = $logger;
        $this->DM = $DM;
        $this->client = $client;
        $this->delivery = Factory::build($configuration);
        $this->stack = new ArrayCollection();
    }

    public function setDelivery(DeliveryInterface $delivery)
    {
        $this->delivery = $delivery;

        return $this;
    }

    public function getDelivery()
    {
        return $this->delivery;
    }

    public function setCapacity($capacity)
    {
        if ($capacity < 1) {
            throw new InvalidArgumentException('Invalid capacity, it should be greater than or equal to 1');
        }

        $this->capacity = (int) $capacity;

        return $this;
    }

    public function getCapacity()
    {
        return $this->capacity;
    }

    public function count()
    {
        return count($this->stack);
    }

    public function drain()
    {
        $removed = array();

        foreach ($this->stack as $uuid => $specification) {
            $stat = $this->client->jobStatus($specification->getJobHandle());

            if ( ! $stat[0]) {
                $removed[] = $uuid;
                $this->removeJob($uuid);
                $this->logger->addInfo(sprintf('Job %s done', $uuid));
            } else {
                if ($stat[1]) {
                    $this->logger->addInfo(sprintf('Job %s running', $uuid));
                } else {
                    $this->logger->addInfo(sprintf('Job %s pending', $uuid));
                }
            }
        }

        return $removed;
    }

    public function fill()
    {
        if (count($this->stack) > $this->ratio * $this->capacity) {
            return;
        }

        /**
         * Before retrieving, items should be booked, so concurrent process
         * do not corrupt datas
         */
        $cursor = $this->DM->createQueryBuilder('Gloubster\\Documents\\Specification')
            ->field('jobHandle')->equals(null)
            ->limit(max(0, $this->capacity - count($this)))
            ->getQuery()
            ->execute();

        /* @var $cursor \Doctrine\ODM\MongoDB\EagerCursor */
        foreach ($cursor as $specification) {

            if ($this->stack->containsKey($specification->getId())) {
                $this->logger->addCritical('Fetch an item that should not exists');
                continue;
            }

            $parameters = $this->parametersToArray($specification->getParameters());

            $query = new Query($specification->getId(), $specification->getJobset()->getFile(), $this->delivery->getName(), $this->delivery->getSignature(), $parameters);

            $specification->setJobHandle(
                $this->addJob(
                    $this->getJobName($specification->getName()), $query
                )
            )->setSubmittedOn(new \DateTime());

            $this->DM->persist(
                $specification
            );

            $this->stack->set($specification->getId(), $specification);
        }

        $this->DM->flush();

        foreach ($this->stack as $uuid => $spec) {
            $specification = $this->DM->getRepository('Gloubster\Documents\Specification')->find($uuid);
        }

        unset($cursor);
    }

    private function parametersToArray(PersistentCollection $parameters)
    {
        $ret = array();

        foreach ($parameters as $parameter) {
            $ret[$parameter->getName()] = $parameter->getValue();
        }

        return $ret;
    }

    private function getJobName($specName)
    {
        switch ($specName) {
            case 'image':
                $name = Query::FUNCTION_TRANSMUTE_IMAGE;
                break;
            default:
                throw new InvalidArgumentException(sprintf('Unknown spec name `%s`', $specName));
                break;
        }

        return $name;
    }

    private function removeJob($key)
    {
        $this->DM->persist(
            $this->updateSpecification(
                $this->stack->get($key), $this->delivery->retrieve($key)
            )
        );

        $this->DM->flush();
        $this->stack->remove($key);
    }

    private function updateSpecification(Specification $specification, Result $result)
    {
        return $specification
                ->setStart($result->getStart())
                ->setStop($result->getStop())
                ->setError((Boolean) $result->getErrors())
                ->setWorkerName($result->getWorkerName())
                ->setDone(true);
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

        /**
         * add exceptions here
         */
        switch ($this->client->returnCode()) {
            case GEARMAN_SUCCESS:
                $this->logger->addInfo(
                    sprintf(
                        'Sending job `%s` with payload %s', $function, serialize($query)
                    )
                );
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

        return $jobHandle;
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
