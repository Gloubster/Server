<?php

namespace Gloubster\Server;

use Gloubster\Message\Presence\WorkerPresence;
use Monolog\Logger;
use Ratchet\ConnectionInterface as Conn;
use Ratchet\Wamp\WampServerInterface;

class WebsocketApplication implements WampServerInterface
{
    private $logger;
    private $subscribedTopics = array();

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function onPublish(Conn $conn, $topic, $event, array $exclude, array $eligible)
    {
        $topic->broadcast($event);
    }

    public function onCall(Conn $conn, $id, $topic, array $params)
    {
        $conn->callError($id, $topic, 'RPC not supported on this demo');
    }

    // No need to anything, since WampServer adds and removes subscribers to Topics automatically
    public function onSubscribe(Conn $conn, $topic)
    {
        // When a visitor subscribes to a topic link the Topic object in a  lookup array
        if (! array_key_exists($topic->getId(), $this->subscribedTopics)) {
            $this->subscribedTopics[$topic->getId()] = $topic;
        }
    }

    public function onUnSubscribe(Conn $conn, $topic)
    {
    }

    public function onOpen(Conn $conn)
    {
        if (!property_exists($conn, 'Session') || !$conn->Session->get('authenticated')) {
            $conn->close(1008);
            $this->logger->addInfo('Reject unauthenticated connection');

            return;
        }
    }

    public function onClose(Conn $conn)
    {
    }

    public function onError(Conn $conn, \Exception $e)
    {
        $this->logger->addError(sprintf('Websocket server error : %s', $e->getMessage()));
    }

    public function onServerInformation(array $data)
    {
        if (! array_key_exists('http://phraseanet.com/gloubster/server-monitor', $this->subscribedTopics)) {
            return;
        }

        $this->subscribedTopics['http://phraseanet.com/gloubster/server-monitor']->broadcast(json_encode($data));
    }

    public function broadcastQueueInformation($data)
    {
        if (! array_key_exists('http://phraseanet.com/gloubster/queue-monitor', $this->subscribedTopics)) {
            return;
        }

        $this->subscribedTopics['http://phraseanet.com/gloubster/queue-monitor']->broadcast(json_encode($data));
    }

    public function broadcastExchangeInformation($data)
    {
        if (! array_key_exists('http://phraseanet.com/gloubster/exchange-monitor', $this->subscribedTopics)) {
            return;
        }

        $this->subscribedTopics['http://phraseanet.com/gloubster/exchange-monitor']->broadcast(json_encode($data));
    }

    public function onPresence(WorkerPresence $presence)
    {
        if (! array_key_exists('http://phraseanet.com/gloubster/monitor', $this->subscribedTopics)) {
            return;
        }

        // re-send the serialized json to all the clients subscribed to that category
        $this->subscribedTopics['http://phraseanet.com/gloubster/monitor']->broadcast($presence->toJson());
    }
}
