<?php

namespace Gloubster\Gearman;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Monolog\Logger;


class Client
{
    const PRIORITY_HIGH = 'high';
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PARALLEL_JOBS = 3;

    protected $client;

    /**
     *
     * @var Logger
     */
    protected $logger;
    protected $jobs;
    protected $dm;

    public function __construct(DocumentManager $DM, Logger $logger)
    {
        $this->client = new \GearmanClient();
        $this->logger = $logger;
        $this->dm = $DM;
        $this->jobs = new ArrayCollection();

        $this->client->setCompleteCallback(array($this, 'onComplete'));
        $this->client->setDataCallback(array($this, 'onData'));
        $this->client->setCreatedCallback(array($this, 'onCreated'));
        $this->client->setExceptionCallback(array($this, 'onException'));
        $this->client->setFailCallback(array($this, 'onFail'));
        $this->client->setStatusCallback(array($this, 'onStatus'));
        $this->client->setWarningCallback(array($this, 'onWarning'));
        $this->client->setWorkloadCallback(array($this, 'onWorkload'));
    }

    public function addServer($server, $port)
    {
        $this->client->addServer($server, $port);
    }

    public function run()
    {
        while (true) {
            $this->addJobs(max(self::PARALLEL_JOBS - count($this->jobs), 0));
            $this->watchJobs();
            $this->logger->addInfo(sprintf('Now using %dMo', memory_get_usage() >> 20));
            sleep(1);
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
                    $this->logger->addInfo(sprintf('Job %s running ; %f%', $uuid, $stat[2] / $stat[3]));
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

                $uuid = $specification->getId();
                $workload = array(
                    'file'       => $jobset->getFile(),
                    'parameters' => array()
                );

                foreach ($specification->getParameters() as $parameter) {
                    $workload['parameters'][$parameter->getName()] = $parameter->getValue();
                }

                $job = "transmute_" . $specification->getName();
                $serializedWorkload = json_encode($workload);

                $this->addJob($job, $serializedWorkload, $uuid);
            }
        }
    }

    private function addJob($function, $workload, $uuid, $priority = self::PRIORITY_NORMAL)
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

        $jobHandle = @$this->client->$doPriority($function, $workload, $uuid);

        switch ($this->client->returnCode()) {
            case GEARMAN_SUCCESS:
                $this->logger->addInfo(
                    sprintf(
                        'Sending job `%s` with payload %s', $function, $workload
                    )
                );
                $this->jobs->set($uuid, $jobHandle);
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

    /**
     *  Set a function to be called on task completion
     */
    public function onComplete(\GearmanTask $task)
    {
        $this->logger->addInfo(
            sprintf(
                'onComplete callback triggered for task %s (jobHandle %s) started with '
                . 'functionName %s', $task->unique(), $task->jobHandle(), $task->functionName()
            )
        );
    }

    /**
     *  Callback function when there is a data packet for a task
     */
    public function onData(\GearmanTask $task)
    {
        $this->logger->addInfo(
            sprintf(
                'onData callback triggered for task %s (jobHandle %s) started with '
                . 'functionName %s', $task->unique(), $task->jobHandle(), $task->functionName()
            )
        );
    }

    /**
     *  Set a callback for when a task is queued
     */
    public function onCreated(\GearmanClient $task)
    {
        $this->logger->addInfo(sprintf('onCreatd callback triggered for client'));
    }

    /**
     *  Set a callback for worker exceptions
     */
    public function onException($data)
    {
        $info = 'unknown information';

        if (is_scalar($data)) {
            $info = $data;
        } elseif (is_array($data)) {
            $info = implode(', ', $data);
        } elseif ($data instanceof \Exception) {
            $info = 'Exception of type `' . getClass($data) . '` with message `' . $data->getmessage() . '`';
        } elseif (is_object($data)) {
            $info = 'data of type ' . get_class($data) . ' returned';
        }

        $this->logger->addInfo(
            sprintf(
                'onException callback triggered %s', $info
            )
        );
    }

    /**
     *  Set callback for job failure
     */
    public function onFail(\GearmanTask $task)
    {
        $this->logger->addInfo(
            sprintf(
                'onFail callback triggered for task %s (jobHandle %s) started with '
                . 'functionName %s', $task->unique(), $task->jobHandle(), $task->functionName()
            )
        );
    }

    /**
     *  Set a callback for collecting task status
     */
    public function onStatus(\GearmanTask $task)
    {
        $this->logger->addInfo(
            sprintf(
                'onStatus callback triggered for task %s (jobHandle %s) started with '
                . 'functionName %s', $task->unique(), $task->jobHandle(), $task->functionName()
            )
        );
    }

    /**
     *  Set a callback for worker warnings
     */
    public function onWarning(\GearmanTask $task)
    {
        $this->logger->addInfo(
            sprintf(
                'onWarning callback triggered for task %s (jobHandle %s) started with '
                . 'functionName %s', $task->unique(), $task->jobHandle(), $task->functionName()
            )
        );
    }

    /**
     *  Set a callback for accepting incremental data updates
     */
    public function onWorkload(\GearmanTask $task)
    {
        $this->logger->addInfo(
            sprintf(
                'onWorkload callback triggered for task %s (jobHandle %s) started with '
                . 'functionName %s', $task->unique(), $task->jobHandle(), $task->functionName()
            )
        );
    }
}

