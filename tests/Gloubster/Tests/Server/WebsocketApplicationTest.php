<?php

namespace Gloubster\Tests\Server;

use Gloubster\Message\Presence\WorkerPresence;
use Gloubster\Server\WebsocketApplication;
use Ratchet\WebSocket\Version\RFC6455\Connection;

class WebsocketApplicationTest extends \PHPUnit_Framework_TestCase
{

    public function testOnPublish()
    {
        $conn = $this->getConn();
        $topic = $this->getTopic();

        $topic->expects($this->once())
            ->method('broadcast')
            ->with($this->equalTo('an-event'));

        $this->getApplication()->onPublish($conn, $topic, 'an-event', array(), array());
    }

    public function testOnCall()
    {
        $conn = $this->getConn();
        $topic = $this->getTopic();

        $conn->expects($this->once())
            ->method('callError');

        $this->getApplication()->onCall($conn, $topic, 'an-event', array(), array());
    }

    public function testOnSubscribe()
    {
        $conn = $this->getConn();
        $topic = $this->getTopic();

        $topic->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('http://example.org#pretty-topic'));

        $this->getApplication()->onSubscribe($conn, $topic);
    }

    public function testOnUnSubscribe()
    {
        $conn = $this->getConn();
        $topic = $this->getTopic();

        $topic->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('http://example.org#pretty-topic'));

        $this->getApplication()->onUnSubscribe($conn, $topic);
    }

    public function testOnOpenAuthorized()
    {
        $session = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\SessionInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $session->expects($this->once())
            ->method('get')
            ->with($this->equalTo('authenticated'))
            ->will($this->returnValue(true));

        $conn = $this->getMockBuilder(__NAMESPACE__ . '\\TestConnection')
            ->disableOriginalConstructor()
            ->getMock();

        $conn->Session = $session;

        $conn->expects($this->never())
            ->method('close');

        $this->getApplication()->onOpen($conn);
    }

    public function testOnOpenNotAuthorized()
    {
        $session = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\SessionInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $session->expects($this->once())
            ->method('get')
            ->with($this->equalTo('authenticated'))
            ->will($this->returnValue(false));

        $conn = $this->getMockBuilder(__NAMESPACE__ . '\\TestConnection')
            ->disableOriginalConstructor()
            ->getMock();

        $conn->Session = $session;

        $conn->expects($this->once())
            ->method('close')
            ->with($this->equalTo(1008));

        $this->getApplication()->onOpen($conn);
    }

    public function testOnClose()
    {
        $this->getApplication()->onClose($this->getConn());
    }

    public function testOnError()
    {
        $data = '';
        $logger = $this->getMockBuilder('Monolog\\Logger')
                    ->disableOriginalConstructor()
                    ->getMock();

        $logger->expects($this->any())
            ->method('addError')
            ->will($this->returnCallback(function($message) use (&$data){
                $data .= $message;
            }));

        $app = new WebsocketApplication($logger);

        $app->onError($this->getConn(), new \Exception('Hello gloubi'));

        $this->assertGreaterThan(0, strpos($data, 'Hello gloubi'));
    }

    public function testOnServerInformationEmpty()
    {
        $this->getApplication()->onServerInformation(array('hello' => 'world'));
    }

    public function testOnServerInformationWithTopic()
    {
        $app = $this->getApplication();
        $topic = $this->getTopic();

        $topic->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('http://phraseanet.com/gloubster/server-monitor'));

        $app->onSubscribe($this->getConn(), $topic);

        $app->onServerInformation(array('hello' => 'world'));
    }

    public function testBroadcastQueueInformationEmpty()
    {
        $this->getApplication()->broadcastQueueInformation(array('hello' => 'world'));
    }

    public function testBroadcastQueueInformationWithTopic()
    {
        $app = $this->getApplication();
        $topic = $this->getTopic();

        $topic->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('http://phraseanet.com/gloubster/queue-monitor'));

        $app->onSubscribe($this->getConn(), $topic);

        $app->broadcastQueueInformation(array('hello' => 'world'));
    }

    public function testBroadcastExchangeInformationEmpty()
    {
        $this->getApplication()->broadcastExchangeInformation(array('hello' => 'world'));
    }

    public function testBroadcastExchangeInformationWithTopic()
    {
        $app = $this->getApplication();
        $topic = $this->getTopic();

        $topic->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('http://phraseanet.com/gloubster/exchange-monitor'));

        $app->onSubscribe($this->getConn(), $topic);

        $app->broadcastExchangeInformation(array('hello' => 'world'));
    }

    public function testOnPresenceEmpty()
    {
        $this->getApplication()->onPresence(new WorkerPresence());
    }

    public function testOnPresenceWithTopic()
    {
        $app = $this->getApplication();
        $topic = $this->getTopic();

        $topic->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('http://phraseanet.com/gloubster/monitor'));

        $app->onSubscribe($this->getConn(), $topic);

        $app->onPresence(new WorkerPresence());
    }

    private function getConn()
    {
        return $this->getMockBuilder('Ratchet\Wamp\WampConnection')
                ->disableOriginalConstructor()
                ->getmock();
    }

    private function getTopic()
    {
        return $this->getMockBuilder('Ratchet\Wamp\Topic')
                ->disableOriginalConstructor()
                ->getmock();
    }

    private function getApplication()
    {
        return new WebsocketApplication(
                    $this->getMockBuilder('Monolog\\Logger')
                    ->disableOriginalConstructor()
                    ->getMock()
        );
    }
}

class TestConnection extends Connection
{
    public $Session;
}
