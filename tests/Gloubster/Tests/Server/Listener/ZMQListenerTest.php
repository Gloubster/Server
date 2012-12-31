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

        $listener = new ZMQListener($context, $this->getLogger(), $conf);
        $listener->listen();

        $listener->on('message', function ($message) use (&$catchMessage) {
            $catchMessage = $message;
        });

        $socket->emit('message', array('GOOD MESSAGE'));

        $this->assertEquals('GOOD MESSAGE', $catchMessage);
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

        $listener = new ZMQListener($context, $this->getLogger(), $conf);
        $listener->listen();

        $listener->on('error', function ($error) use (&$catchError) {
            $catchError = $error;
        });

        $socket->emit('error', array($exception));

        $this->assertEquals($exception, $catchError);
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
