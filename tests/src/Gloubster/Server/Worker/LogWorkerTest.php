<?php

namespace Gloubster\Server\Worker;

use Gloubster\Mocks\DeliveryMock;
use Gloubster\Job\ImageJob;
use PhpAmqpLib\Message\AMQPMessage;

require_once __DIR__ . '/../../Mocks/DeliveryMock.php';

class LogWorkerTest extends \PHPUnit_Framework_TestCase
{

    public function testRun()
    {
        $dm = $this->getMockBuilder('Doctrine\\ODM\\MongoDB\\DocumentManager')
            ->disableOriginalConstructor()
            ->getMock();

        $channel = $this->getMockBuilder('PhpAmqpLib\Channel\AMQPChannel')
            ->disableOriginalConstructor()
            ->getMock();

        $message = $this->getMockBuilder('PhpAmqpLib\Message\AMQPMessage')
            ->disableOriginalConstructor()
            ->getMock();

        $that = $this;

        $logger = $this->getMockBuilder('Monolog\\Logger')
            ->disableOriginalConstructor()
            ->getMock();

        $worker = $this->getMock('Gloubster\Server\Worker\LogWorker', array('process'), array($dm, $channel, $logger));

        $channel->expects($this->any())
            ->method('basic_consume')
            ->will($this->returnCallback(function($expectedQueue, $arg, $arg, $arg, $arg, $arg, $callback) use ($worker, $message, $that) {
                $callback($message);
            }));

        $worker->expects($this->exactly(5))
            ->method('process');

        $worker->run(5);
    }

    private function getMessage($body, $deliveryTag)
    {
        $message = new AMQPMessage($body);
        $message->delivery_info['delivery_tag'] = $deliveryTag;

        return $message;
    }

    /**
     * @covers Gloubster\Server\Worker\LogWorker::process
     */
    public function testProcessErrorMessage()
    {
        $that = $this;
        $dm = $this->getMockBuilder('Doctrine\\ODM\\MongoDB\\DocumentManager')
            ->disableOriginalConstructor()
            ->getMock();

        $channel = $this->getMockBuilder('PhpAmqpLib\Channel\AMQPChannel')
            ->disableOriginalConstructor()
            ->getMock();

        $deliveryTag = 'deliveryTag' . mt_rand();

        $channel->expects($this->once())
            ->method('basic_ack')
            ->will($this->returnCallback(function($tag) use ($deliveryTag, $that) {
                $that->assertEquals($deliveryTag, $tag);
            }));

        $logger = $this->getMockBuilder('Monolog\\Logger')
            ->disableOriginalConstructor()
            ->getMock();

        $worker = $this->getMock('Gloubster\Server\Worker\LogWorker', null, array($dm, $channel, $logger));

        $dm->expects($this->once())
            ->method('flush')
            ->will($this->returnCallback(function($docJob) use ($that){
                $that->assertInstanceOf('Gloubster\Documents\Garbage', $docJob);
                /* @var $docJob \Gloubster\Documents\Job */
                $this->assertEquals(serialize(array('hello'=>'world')), $docJob->getData());
            }));

        $worker->process($this->getMessage(serialize(array('hello'=>'world')), $deliveryTag));
    }


    /**
     * @covers Gloubster\Server\Worker\LogWorker::process
     */
    public function testProcessGoodMessage()
    {
        $that = $this;
        $dm = $this->getMockBuilder('Doctrine\\ODM\\MongoDB\\DocumentManager')
            ->disableOriginalConstructor()
            ->getMock();

        $channel = $this->getMockBuilder('PhpAmqpLib\Channel\AMQPChannel')
            ->disableOriginalConstructor()
            ->getMock();

        $deliveryTag = 'deliveryTag' . mt_rand();

        $channel->expects($this->once())
            ->method('basic_ack')
            ->will($this->returnCallback(function($tag) use ($deliveryTag, $that) {
                $that->assertEquals($deliveryTag, $tag);
            }));

        $logger = $this->getMockBuilder('Monolog\\Logger')
            ->disableOriginalConstructor()
            ->getMock();

        $worker = $this->getMock('Gloubster\Server\Worker\LogWorker', null, array($dm, $channel, $logger));

        $delivery = new DeliveryMock('/path/to/target');

        $job = new ImageJob('/path/to/source', $delivery);
        $job->setDeliveryDuration(123456)
            ->setEnd(microtime(true))
            ->setError(true)
            ->setErrorMessage('May the force be with you')
            ->setParameters(array('key' => 'value'))
            ->setProcessDuration(654321)
            ->setReceipts(array())
            ->setWorkerId('Worker_id');

        $dm->expects($this->once())
            ->method('flush')
            ->will($this->returnCallback(function($docJob) use ($job, $that){
                $that->assertInstanceOf('Gloubster\Documents\Job', $docJob);
                /* @var $docJob \Gloubster\Documents\Job */
                $this->assertEquals($job->isOnError(), $docJob->getError());
                $this->assertEquals($job->getErrorMessage(), $docJob->getErrorMessage());
                $this->assertEquals($job->getWorkerId(), $docJob->getWorkerId());
                $this->assertEquals($job->getBeginning(), $docJob->getBeginning());
                $this->assertEquals($job->getEnd(), $docJob->getEnd());
                $this->assertEquals($job->getDelivery()->getId(), $docJob->getDeliveryId());
                $this->assertEquals($job->getDelivery()->getName(), $docJob->getDelivery());
                $this->assertEquals($job->getProcessDuration(), $docJob->getProcessDuration());
                $this->assertEquals($job->getDeliveryDuration(), $docJob->getDeliveryDuration());
                $this->assertEquals($job->getRoutingKey(), $docJob->getRoutingKey());
                $this->assertEquals($job->getExchangeName(), $docJob->getExchangeName());
            }));

        $worker->process($this->getMessage(serialize($job), $deliveryTag));
    }
}
