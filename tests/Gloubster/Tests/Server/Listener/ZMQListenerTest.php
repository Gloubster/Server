<?php

namespace Gloubster\Tests\Server\Listener;

use Gloubster\Server\Listener\ZMQListener;
use Gloubster\Tests\GloubsterTest;

/**
 * @covers Gloubster\Server\Listener\ZMQListener
 */
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

        $context->expects($this->any())
            ->method('getSocket')
            ->will($this->returnvalue($socket));

        new ZMQListener($context, $this->getLogger(), $conf);
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
    public function itShouldListen()
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

        $listener = new ZMQListener($context, $this->getLogger(), $conf);
        $listener->listen();
    }

    /** @test */
    public function requestsShouldEmitMessages()
    {
        $catchMessage = null;
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
            $this->getEventLoop()
        );

        $context->expects($this->any())
            ->method('getSocket')
            ->will($this->returnvalue($socket));

        $handler = $this->getMessageHandlerMock();
        $handler->expects($this->once())
            ->method('receive')
            ->with($this->equalTo('GOOD MESSAGE'));

        $listener = new ZMQListener($context, $this->getLogger(), $conf);
        $listener->attach($handler);
        $listener->listen();

        $socket->emit('message', array('GOOD MESSAGE'));
    }

    /** @test */
    public function requestsShutdownShouldUnbind()
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
            ->method('unbind')
            ->with($this->equalTo('tcp://localhost:55672'));

        $context->expects($this->any())
            ->method('getSocket')
            ->will($this->returnvalue($socket));

        $listener = new ZMQListener($context, $this->getLogger(), $conf);
        $listener->attach($this->getMessageHandlerMock());
        $listener->shutdown();
    }

    /** @test */
    public function requestsShouldTriggersGloubsterErrorCallback()
    {
        $catchError = null;
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
            $this->getEventLoop()
        );

        $context->expects($this->any())
            ->method('getSocket')
            ->will($this->returnvalue($socket));

        $handler = $this->getMessageHandlerMock();
        $handler->expects($this->once())
            ->method('error')
            ->with($this->equalTo($exception));

        $listener = new ZMQListener($context, $this->getLogger(), $conf);
        $listener->attach($handler);
        $listener->listen();

        $socket->emit('error', array($exception));
    }

    /**
     * @test
     * @expectedException Gloubster\Exception\InvalidArgumentException
     */
    public function constructWithoutTransportMustFail()
    {
        $conf = array(
            'address' => 'localhost',
            'port'    => 55672,
        );

        new ZMQListener($this->getContext(), $this->getLogger(), $conf);
    }

    /**
     * @test
     * @expectedException Gloubster\Exception\InvalidArgumentException
     */
    public function constructWithoutHostMustFail()
    {
        $conf = array(
            'transport' => 'tcp',
            'port'      => 55672,
        );

        new ZMQListener($this->getContext(), $this->getLogger(), $conf);
    }

    /**
     * @test
     * @expectedException Gloubster\Exception\InvalidArgumentException
     */
    public function constructWithoutPortMustFail()
    {
        $conf = array(
            'transport' => 'tcp',
            'address'   => 'localhost',
        );

        new ZMQListener($this->getContext(), $this->getLogger(), $conf);
    }

    private function getContext()
    {
        $loop = $this->getEventLoop();

        $context = $this->getMockBuilder('ZMQContext')
            ->disableOriginalConstructor()
            ->getMock();

        return new \React\ZMQ\Context($loop, $context);
    }
}
