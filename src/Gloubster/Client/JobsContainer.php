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
    const WAITING_STATUS = 'waiting';

    protected $DM;
    protected $client;
    protected $logger;

    /**
     *
     * @var DeliveryInterface
     */
    protected $delivery;
    protected $stack;
    protected $ratio = 0.7;
    protected $capacity = 500;

    public function __construct(\GearmanClient $client, Configuration $configuration, DocumentManager $DM, Logger $logger)
    {
        $this->logger = $logger;
        $this->DM = $DM;
        $this->client = $client;
        $this->delivery = Factory::build($configuration);
        $this->stack = new ArrayCollection();
        $this->loops = 0;
        $this->time = 0;
        $this->loopsRemove = 0;
        $this->timeRemove = 0;
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
            $startLoop = $start = microtime(true);
            $stat = $this->client->jobStatus($specification->getJobHandle());
            $stop = microtime(true);

            $this->logger->addInfo("jobstatus got in " . ($stop - $start));

            if ( ! $stat[0]) {
                $this->logger->addInfo('sending the remove signal');
                $startRemove = microtime(true);
                $removed[] = $uuid;
                $this->removeJob($uuid);
                $this->logger->addInfo(sprintf('Job %s done', $uuid));
                $this->timeRemove += (microtime(true) - $startRemove);
                $this->loopsRemove ++;
            } else {
                if ($stat[1]) {
                    $this->logger->addInfo(sprintf('Job %s running', $uuid));
                } else {
                    $this->logger->addInfo(sprintf('Job %s pending', $uuid));
                }
            }
            $this->time += (microtime(true) - $startLoop);
            $this->loops ++;
        }
        $this->logger->addInfo('sending the flush signal');
        $this->DM->flush();

        if ($this->loops) {
            $this->logger->addInfo("Average time per loop : " . ($this->time / $this->loops));
        }
        if ($this->loopsRemove) {
            $this->logger->addInfo("Average time per remove : " . ($this->timeRemove / $this->loopsRemove));
        }

        return $removed;
    }

    public function fill()
    {
        if (count($this->stack) > $this->ratio * $this->capacity) {
            return;
        }

        $n = $this->capacity - count($this);

        while ($n > 0) {
            $specification = $this->DM->createQueryBuilder()
                ->findAndUpdate('Gloubster\Documents\Specification')
                ->returnNew(true)
                ->field('jobHandle')->equals(null)
                ->field('jobHandle')->set(self::WAITING_STATUS)
                ->getQuery()
                ->execute();

            if (null === $specification) {
                break;
            }

            $priority = self::PRIORITY_HIGH;

            foreach ($specification->getJobset()->getSpecifications() as $spec) {
                if ($spec->getJobHandle() !== null && $spec->getId() != $specification->getId()) {
                    $priority = self::PRIORITY_NORMAL;
                    break;
                }
            }

            if ($this->stack->containsKey($specification->getId())) {
                $this->logger->addCritical('Fetch an item that should not exists');
                continue;
            }

            if ($specification->getJobHandle() !== self::WAITING_STATUS) {
                $this->logger->addCritical('Fetch an item that should not exists');
                continue;
            }

            $parameters = $this->parametersToArray($specification->getParameters());

            $query = new Query($specification->getId(), $specification->getJobset()->getFile(), $this->delivery->getName(), $this->delivery->getSignature(), $parameters);

            $this->DM->persist(
                $specification->setJobHandle(
                    $this->addJob(
                        $this->getJobName($specification->getName()), $query, $priority
                    )
                )->setSubmittedOn(new \DateTime())
            );

            $this->stack->set($specification->getId(), $specification);

            $n --;
        }

        $this->DM->flush();
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

        try {
            $start = microtime(true);
            $spec = $this->stack->get($key);
            $stop = microtime(true);

            $this->logger->addInfo("step 2 " . ($stop - $start));


            $start = microtime(true);
            $this->logger->addInfo("removing job... about to retrieve ...");
            try {
                $retrieved = $this->delivery->retrieve($key);
            } catch (\Exception $e) {
                $this->logger->addInfo('EXCEPTION : ' . $e->getMessage());

                $spec->setJobHandle(null);
                $this->DM->persist($spec);
                $this->DM->flush();
                $this->stack->remove($key);

                return;
            }

            $this->logger->addInfo("removing job... retrieved");
            $stop = microtime(true);

            $this->logger->addInfo("step 1 " . ($stop - $start));

            $start = microtime(true);
            $update = $this->updateSpecification($spec, $retrieved);
            $stop = microtime(true);

            $this->logger->addInfo("step 3 " . ($stop - $start));

            $start = microtime(true);
            $this->DM->persist($update);
            $stop = microtime(true);

            $this->logger->addInfo("step 4 " . ($stop - $start));

            $start = microtime(true);
            $this->stack->remove($key);
            $stop = microtime(true);

            $this->logger->addInfo("step 5 " . ($stop - $start));
        } catch (\Exception $e) {
            $this->logger->addCritical('UNHANDELD CRITICAL EXCEPTION ' . $e->getMessage());
        }
    }

    private function updateSpecification(Specification $specification, Result $result)
    {
        return $specification
                ->setStart($result->getStart())
                ->setStop($result->getStop())
                ->setError((Boolean) $result->getErrors())
                ->setWorkerName($result->getWorkerName())
                ->setDone(true)
                ->setTimers(serialize($result->getTimers()));

//        foreach ($result->getTimers() as $key => $time) {
//            $timer = new \Gloubster\Documents\Timer();
//            $timer->setName('indice-')->setValue(0.12);
//            $this->DM->persist($timer);
//            $specification->addTimers($timer);
//            $this->logger->addInfo(sprintf('add timer with name %s and value %s ', $key, $time));
//        }
//        $this->DM->persist($specification);
//        $this->DM->flush();
//        return $specification;
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
                        'Sending job `%s` with payload %s and priority %s', $function, serialize($query), $priority
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
