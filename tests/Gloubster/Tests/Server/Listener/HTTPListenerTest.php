<?php

namespace Gloubster\Tests\Server\Listener;

use Gloubster\Server\Listener\HTTPListener;
use Gloubster\Tests\GloubsterTest;
use React\Http\Server as HttpServer;
use React\Http\Request as HttpRequest;

/**
 * @covers Gloubster\Server\Listener\HTTPListener
 */
class HTTPListenerTest extends GloubsterTest
{
    /** @test */
    public function itShouldConstruct()
    {
        new HTTPListener($this->getReactHttpServerMock());
    }

    /** @test */
    public function itShouldCreate()
    {
        HTTPListener::create($this->getServer(), array('host' => 'localhost', 'port' => 12345));
    }

    /** @test */
    public function itShouldAttach()
    {
        $gloubster = $this->getServer();
        $server = $this->getReactHttpServerMock();

        $httpListener = new HTTPListener($server);
        $httpListener->attach($gloubster);
    }

    /** @test */
    public function requestsShouldTriggersGloubsterCallbacks()
    {
        $gloubster = $this->getGloubsterServerMock();
        $gloubster->expects($this->once())
            ->method('incomingMessage')
            ->with($this->equalTo('GOOD MESSAGE'));

        $server = new HttpServer($this->getReactSocketServerMock());

        $httpListener = new HTTPListener($server);
        $httpListener->attach($gloubster);

        $request = new HttpRequest('GET', '/');
        $response = $this->getReactHttpResponseMock();

        $server->emit('request', array($request, $response));

        $request->emit('data', array('GOOD ME'));
        $request->emit('data', array('SSAGE'));

        $request->emit('end', array());
    }

    /** @test */
    public function requestsShouldTriggersGloubsterErrorCallback()
    {
        $exception = new \Exception('This is an exception');

        $gloubster = $this->getGloubsterServerMock();
        $gloubster->expects($this->once())
            ->method('incomingError')
            ->with($this->equalTo($exception));

        $server = new HttpServer($this->getReactSocketServerMock());

        $httpListener = new HTTPListener($server);
        $httpListener->attach($gloubster);

        $request = new \React\Http\Request('GET', '/');
        $response = $this->getReactHttpResponseMock();

        $server->emit('request', array($request, $response));

        $request->emit('error', array($exception));
    }

    /**
     * @test
     * @expectedException Gloubster\Exception\InvalidArgumentException
     */
    public function createShouldFailWithoutHost()
    {
        HTTPListener::create($this->getServer(), array('port' => 12345));
    }

    /**
     * @test
     * @expectedException Gloubster\Exception\RuntimeException
     */
    public function createShouldFailIfPortIsAlreadyUsed()
    {
        $options = array('host' => '127.0.0.1', 'port' => 12345);

        HTTPListener::create($this->getServer(), $options);
        HTTPListener::create($this->getServer(), $options);
    }

    /**
     * @test
     * @expectedException Gloubster\Exception\InvalidArgumentException
     */
    public function createShouldFailWithoutPort()
    {
        HTTPListener::create($this->getServer(), array('host' => 'localhost'));
    }

    private function getReactHttpResponseMock()
    {
        return $this->getMockBuilder('React\\Http\\Response')
                ->disableOriginalConstructor()
                ->getMock();
    }

    private function getReactSocketServerMock()
    {
        return $this->getMockBuilder('React\\Socket\\ServerInterface')
                ->disableOriginalConstructor()
                ->getMock();
    }

    private function getReactHttpServerMock()
    {
        return $this->getMockBuilder('React\\Http\\Server')
                ->disableOriginalConstructor()
                ->getMock();
    }
}
