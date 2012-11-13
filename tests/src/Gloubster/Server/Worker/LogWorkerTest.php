<?php

namespace Gloubster\Server\Worker;

use Doctrine\ODM\MongoDB\DocumentManager;
use Gloubster\Queue;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

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

        $queue = Queue::LOGS;
        $that = $this;

        $logger = $this->getMockBuilder('Monolog\\Logger')
            ->disableOriginalConstructor()
            ->getMock();

        $worker = $this->getMock('Gloubster\Server\Worker\LogWorker', array('process'), array($dm, $channel, $queue, $logger));

        $channel->expects($this->any())
            ->method('basic_consume')
            ->will($this->returnCallback(function($expectedQueue, $arg, $arg, $arg, $arg, $arg, $callback) use ($worker, $message, $queue, $that) {
                $callback($message);
                $that->assertEquals($queue, $expectedQueue);
            }));

        $worker->expects($this->exactly(5))
            ->method('process');

        $worker->run(5);
    }

    /**
     * @covers Gloubster\Server\Worker\LogWorker::process
     * @todo   Implement testProcess().
     */
    public function testProcess()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
