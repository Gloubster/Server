<?php

namespace Gloubster\Tests\Server\Listener;

use Gloubster\Server\Listener\HTTPListener;
use Gloubster\Tests\GloubsterTest;

class HTTPListenerTest extends GloubsterTest
{

    /** @test */
    public function itShouldConstruct()
    {
        $server = $this->getMockBuilder('React\\Http\\Server')
            ->disableOriginalConstructor()
            ->getMock();

        $loop = $this->getMockBuilder('React\\EventLoop\\LoopInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $httpListener = new HTTPListener($server);
    }

    /** @test */
    public function itShouldCreate()
    {
        $loop = $this->getMockBuilder('React\\EventLoop\\LoopInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $this->getMockBuilder('Monolog\\Logger')
            ->disableOriginalConstructor()
            ->getMock();

        $httpListener = HTTPListener::create($this->getServer(), array('host'=>'localhost', 'port'=>12345));
    }

    /** @test */
    public function itShouldAttach()
    {
        $gloubster = $this->getServer();

        $server = $this->getMockBuilder('React\\Http\\Server')
            ->disableOriginalConstructor()
            ->getMock();

        $loop = $this->getMockBuilder('React\\EventLoop\\LoopInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $httpListener = new HTTPListener($server);
        $httpListener->attach($gloubster);
    }

    /** @test */
    public function requestsShouldTriggersGloubsterCallbacks()
    {
        $gloubster = $this->getMockBuilder('Gloubster\\Server\\GloubsterServerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $gloubster->expects($this->once())
            ->method('incomingMessage')
            ->with($this->equalTo('GOOD MESSAGE'));

        $server = new \React\Http\Server($this->getMockBuilder('React\\Socket\\ServerInterface')
            ->disableOriginalConstructor()
            ->getMock());

        $httpListener = new HTTPListener($server);
        $httpListener->attach($gloubster);

        $request = new \React\Http\Request('GET', '/');

        $response = $this->getMockBuilder('React\\Http\\Response')
            ->disableOriginalConstructor()
            ->getMock();

        $server->emit('request', array($request, $response));

        $request->emit('data', array('GOOD ME'));
        $request->emit('data', array('SSAGE'));

        $request->emit('end', array());
    }

    /** @test */
    public function requestsShouldTriggersGloubsterErrorCallback()
    {
        $exception = new \Exception('This is an exception');

        $gloubster = $this->getMockBuilder('Gloubster\\Server\\GloubsterServerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $gloubster->expects($this->once())
            ->method('incomingError')
            ->with($this->equalTo($exception));

        $server = new \React\Http\Server($this->getMockBuilder('React\\Socket\\ServerInterface')
            ->disableOriginalConstructor()
            ->getMock());

        $httpListener = new HTTPListener($server);
        $httpListener->attach($gloubster);

        $request = new \React\Http\Request('GET', '/');

        $response = $this->getMockBuilder('React\\Http\\Response')
            ->disableOriginalConstructor()
            ->getMock();

        $server->emit('request', array($request, $response));

        $request->emit('error', array($exception));
    }

    /**
     * @test
     * @expectedException Gloubster\Exception\InvalidArgumentException
     */
    public function createShouldFailWithoutHost()
    {
        $httpListener = HTTPListener::create($this->getServer(), array('port'=>12345));
    }

    /**
     * @test
     * @expectedException Gloubster\Exception\RuntimeException
     */
    public function createShouldFailIfPortIsAlreadyUsed()
    {
        $loop = $this->getMockBuilder('React\\EventLoop\\LoopInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $this->getMockBuilder('Monolog\\Logger')
            ->disableOriginalConstructor()
            ->getMock();

        $httpListener = HTTPListener::create($this->getServer(), array('host' => '127.0.0.1', 'port'=>12345));
        $httpListener = HTTPListener::create($this->getServer(), array('host' => '127.0.0.1', 'port'=>12345));
    }

    /**
     * @test
     * @expectedException Gloubster\Exception\InvalidArgumentException
     */
    public function createShouldFailWithoutPort()
    {
        $httpListener = HTTPListener::create($this->getServer(), array('host'=>'localhost'));
    }
}
