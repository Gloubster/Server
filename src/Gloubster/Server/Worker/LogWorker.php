<?php

namespace Gloubster\Server\Worker;

use Doctrine\ODM\MongoDB\DocumentManager;
use Gloubster\Documents\Job;
use Gloubster\Documents\Garbage;
use Gloubster\Job\JobInterface;
use Gloubster\Queue;
use Monolog\Logger;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class LogWorker
{
    /**
     * @var AMQPChannel
     */
    private $channel;
    private $dm;

    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(DocumentManager $dm, AMQPChannel $channel, Logger $logger)
    {
        $this->dm = $dm;
        $this->channel = $channel;
        $this->logger = $logger;
    }

    public function run($iterations = true)
    {
        while ($iterations) {
            $this->logger->addDebug(sprintf('Current memory usage : %s Mo', round(memory_get_usage() / (1024 * 1024),3)));
            try {
                $this->logger->addInfo('Waiting for messages ...');
                $this->channel->basic_consume(Queue::LOGS, null, false, true, false, false, array($this, 'process'));

                while (count($this->channel->callbacks)) {
                    $this->channel->wait();
                }
            } catch (\Exception $e) {
                $this->logger->addError(sprintf('Gloubster Log process failed : %s', $e->getMessage()));
            }
            $iterations--;
        }

        return $this;
    }

    public function process(AMQPMessage $message)
    {
        $this->logger->addInfo(sprintf('Processing job %s', $message->delivery_info['delivery_tag']));

        $job = unserialize($message->body);

        if ($job instanceof JobInterface) {
            // this is a log message
            $this->saveJob($job);
        } else {
            $this->saveGarbage($message->body);
        }

        $this->channel->basic_ack($message->delivery_info['delivery_tag']);
    }

    private function saveJob(JobInterface $job)
    {
        $document = new Job();
        $document->setBeginning($job->getBeginning())
            ->setDelivery($job->getDelivery()->getName())
            ->setDeliveryDuration($job->getDeliveryDuration())
            ->setDeliveryId($job->getDelivery()->getId())
            ->setEnd($job->getEnd())
            ->setError($job->isOnError())
            ->setErrorMessage($job->getErrorMessage())
            ->setExchangeName($job->getExchangeName())
            ->setProcessDuration($job->getProcessDuration())
            ->setRoutingKey($job->getRoutingKey())
            ->setWorkerId($job->getWorkerId());

        $this->dm->flush($document);

        return $this;
    }

    private function saveGarbage($data)
    {
        $document = new Garbage();

        $this->dm->flush($document->setData($data));

        return $this;
    }
}
