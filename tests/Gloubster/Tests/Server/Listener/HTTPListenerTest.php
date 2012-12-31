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
        new HTTPListener($this->getReactHttpServerMock(), $this->getReactSocketServerMock(), $this->getLogger());
    }

    /** @test */
    public function itShouldCreate()
    {
        HTTPListener::create($this->getServer(), array('host' => 'localhost', 'port' => 12345));
    }

    /** @test */
    public function itShouldNotListen()
    {
        $reactor = $this->getReactSocketServerMock();
        $server = new HttpServer($reactor);

        $host = 'bel.host';
        $port = '8080';

        $httpListener = new HTTPListener($server, $reactor, $this->getLogger(), $host, $port);
        $reactor->expects($this->never())
            ->method('listen');
    }

    /** @test */
    public function itShouldListen()
    {
        $reactor = $this->getReactSocketServerMock();
        $server = new HttpServer($reactor);

        $host = 'bel.host';
        $port = '8080';

        $httpListener = new HTTPListener($server, $reactor, $this->getLogger(), $host, $port);

        $reactor->expects($this->once())
            ->method('listen')
            ->with($this->equalTo($port), $this->equalTo($host));

        $httpListener->listen();
    }

    /** @test */
    public function requestsShouldTriggersGloubsterCallbacks()
    {
        $catchMessage = null;

        $reactor = $this->getReactSocketServerMock();
        $server = new HttpServer($reactor);

        $httpListener = new HTTPListener($server, $reactor, $this->getLogger());
        $httpListener->listen();

        $httpListener->on('message', function ($message) use (&$catchMessage) {
            $catchMessage = $message;
        });

        $request = new HttpRequest('GET', '/');
        $response = $this->getReactHttpResponseMock();

        $server->emit('request', array($request, $response));

        $request->emit('data', array('GOOD ME'));
        $request->emit('data', array('SSAGE'));

        $request->emit('end', array());

        $this->assertEquals('GOOD MESSAGE', $catchMessage);
    }

    /** @test */
    public function requestsShutdownShouldShutdownReactor()
    {
        $reactor = $this->getReactSocketServerMock();
        $server = new HttpServer($reactor);

        $reactor->expects($this->once())
            ->method('shutdown');

        $httpListener = new HTTPListener($server, $reactor, $this->getLogger());
        $httpListener->shutdown();
    }

    /** @test */
    public function requestsShouldTriggersGloubsterErrorCallback()
    {
        $catchError = null;
        $exception = new \Exception('This is an exception');

        $reactor = $this->getReactSocketServerMock();
        $server = new HttpServer($reactor);

        $httpListener = new HTTPListener($server, $reactor, $this->getLogger());
        $httpListener->listen();

        $httpListener->on('error', function ($error) use (&$catchError) {
            $catchError = $error;
        });

        $request = new \React\Http\Request('GET', '/');
        $response = $this->getReactHttpResponseMock();

        $server->emit('request', array($request, $response));

        $request->emit('error', array($exception));

        $this->assertEquals($exception, $catchError);
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

        $listener = HTTPListener::create($this->getServer(), $options);
        $listener->listen();

        $listener = HTTPListener::create($this->getServer(), $options);
        $listener->listen();
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
        return $this->getMockBuilder('React\\Socket\\Server')
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
