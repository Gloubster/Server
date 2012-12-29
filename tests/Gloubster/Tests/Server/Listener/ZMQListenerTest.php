<?php

namespace Gloubster\Tests\Server\Listener;

use Gloubster\Server\Listener\ZMQListener;
use Gloubster\Tests\GloubsterTest;

class ZMQListenerTest extends GloubsterTest
{

    /** @test */
    public function itShouldConstruct()
    {
        $context = $this->getContext();

        $conf = array(
            'transport' => 'tcp',
            'address'   => 'localhost',
            'port'      => 55672,
        );

        $socket = $this->getMockBuilder('ZMQSocket')
            ->disableOriginalConstructor()
            ->getMock();

        $socket->expects($this->once())
            ->method('bind')
            ->with($this->equalTo('tcp://localhost:55672'));

        $context->expects($this->any())
            ->method('getSocket')
            ->will($this->returnvalue($socket));

        $logger = $this->getMockBuilder('Monolog\\Logger')
            ->disableOriginalConstructor()
            ->getMock();

        new ZMQListener($context, $logger, $conf);
    }

    /** @test */
    public function itShouldCreate()
    {
        $options = array(
            'transport' => 'ipc',
            'address'   => 'localhost',
            'port'      => 15672,
        );

        ZMQListener::create($this->getServer(), $options);
    }

    /** @test */
    public function itShouldAttach()
    {
        $options = array(
            'transport' => 'ipc',
            'address'   => 'localhost',
            'port'      => 15672,
        );

        $gloubster = $this->getServer();

        $listener = ZMQListener::create($gloubster, $options);
        $listener->attach($gloubster);
    }

    /** @test */
    public function requestsShouldTriggersGloubsterCallbacks()
    {
        $context = $this->getContext();

        $conf = array(
            'transport' => 'tcp',
            'address'   => 'localhost',
            'port'      => 55672,
        );

        $socket = new \React\ZMQ\SocketWrapper(
            $this->getMockBuilder('ZMQSocket')
                ->disableOriginalConstructor()
                ->getMock(),
            $this->getMockBuilder('React\\EventLoop\\LoopInterface')
                ->disableOriginalConstructor()
                ->getMock()
        );

        $context->expects($this->any())
            ->method('getSocket')
            ->will($this->returnvalue($socket));

        $logger = $this->getMockBuilder('Monolog\\Logger')
            ->disableOriginalConstructor()
            ->getMock();

        $listener = new ZMQListener($context, $logger, $conf);
        $gloubster = $this->getMockBuilder('Gloubster\\Server\\GloubsterServerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $listener->attach($gloubster);

        $gloubster->expects($this->once())
            ->method('incomingMessage')
            ->with($this->equalTo('GOOD MESSAGE'));

        $socket->emit('message', array('GOOD MESSAGE'));
    }

    /** @test */
    public function requestsShouldTriggersGloubsterErrorCallback()
    {
        $exception = new \Exception('A pretty cool exception');

        $context = $this->getContext();

        $conf = array(
            'transport' => 'tcp',
            'address'   => 'localhost',
            'port'      => 55672,
        );

        $socket = new \React\ZMQ\SocketWrapper(
            $this->getMockBuilder('ZMQSocket')
                ->disableOriginalConstructor()
                ->getMock(),
            $this->getMockBuilder('React\\EventLoop\\LoopInterface')
                ->disableOriginalConstructor()
                ->getMock()
        );

        $context->expects($this->any())
            ->method('getSocket')
            ->will($this->returnvalue($socket));

        $logger = $this->getMockBuilder('Monolog\\Logger')
            ->disableOriginalConstructor()
            ->getMock();

        $listener = new ZMQListener($context, $logger, $conf);
        $gloubster = $this->getMockBuilder('Gloubster\\Server\\GloubsterServerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $listener->attach($gloubster);

        $gloubster->expects($this->once())
            ->method('incomingError')
            ->with($this->equalTo($exception));

        $socket->emit('error', array($exception));
    }

    /**
     * @test
     * @expectedException Gloubster\Exception\InvalidArgumentException
     */
    public function constructWithoutTransportMustFail()
    {
        $context = $this->getContext();

        $conf = array(
            'address'   => 'localhost',
            'port'      => 55672,
        );

        $logger = $this->getMockBuilder('Monolog\\Logger')
            ->disableOriginalConstructor()
            ->getMock();

        new ZMQListener($context, $logger, $conf);
    }

    /**
     * @test
     * @expectedException Gloubster\Exception\InvalidArgumentException
     */
    public function constructWithoutHostMustFail()
    {
        $context = $this->getContext();

        $conf = array(
            'transport' => 'tcp',
            'port'      => 55672,
        );

        $logger = $this->getMockBuilder('Monolog\\Logger')
            ->disableOriginalConstructor()
            ->getMock();

        new ZMQListener($context, $logger, $conf);
    }

    /**
     * @test
     * @expectedException Gloubster\Exception\InvalidArgumentException
     */
    public function constructWithoutPortMustFail()
    {
        $context = $this->getContext();

        $conf = array(
            'transport' => 'tcp',
            'address'   => 'localhost',
        );

        $logger = $this->getMockBuilder('Monolog\\Logger')
            ->disableOriginalConstructor()
            ->getMock();

        new ZMQListener($context, $logger, $conf);
    }

    private function getContext()
    {
        $loop = $this->getMockBuilder('React\\EventLoop\\LoopInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $context = $this->getMockBuilder('ZMQContext')
            ->disableOriginalConstructor()
            ->getMock();

        return new \React\ZMQ\Context($loop, $context);
    }
}
