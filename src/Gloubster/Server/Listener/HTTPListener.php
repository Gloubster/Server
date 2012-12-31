<?php

namespace Gloubster\Server\Listener;

use Evenement\EventEmitter;
use Gloubster\Server\GloubsterServerInterface;
use Gloubster\Exception\InvalidArgumentException;
use Gloubster\Exception\RuntimeException;
use Monolog\Logger;
use React\Http\Server;
use React\Socket\Server as Reactor;

class HTTPListener extends EventEmitter implements JobListenerInterface
{
    private $host;
    private $port;
    private $logger;
    private $server;
    private $socket;

    public function __construct(Server $server, Reactor $socket, Logger $logger, $host = '0.0.0.0', $port = 80)
    {
        $this->host = $host;
        $this->port = $port;
        $this->socket = $socket;
        $this->logger = $logger;
        $this->server = $server;

        $listener = $this;

        $this->server->on('request', function ($request, $response) use ($listener) {
            $response->writeHead(200);
            $response->end();

            $data = (object) array('message' => '');

            $request->on('data', function ($chunk) use ($data) {
                $data->message .= $chunk;
            });

            $request->on('end', function() use ($data, $listener) {
                $listener->emit('message', array($data->message));
            });

            $request->on('error', function ($error) use ($listener) {
                $listener->emit('error', array($error));
            });
        });
    }

    /**
     * {@inheritdoc}
     */
    public function listen()
    {
        try {
            $this->socket->listen($this->port, $this->host);
        } catch (\Exception $e) {
            throw new RuntimeException(sprintf('Unable to listen to %s:%s', $this->host, $this->port));
        }
        $this->logger->addInfo(sprintf('Listening for message on HTTP %s:%s', $this->host, $this->port));
    }

    /**
     * {@inheritdoc}
     */
    public function shutdown()
    {
        $this->socket->shutdown();
        $this->logger->addInfo(sprintf('Stop Listening on HTTP protocol %s:%s', $this->host, $this->port));
    }

    /**
     * {@inheritdoc}
     */
    public static function create(GloubsterServerInterface $server, array $options)
    {
        if (!isset($options['port'])) {
            throw new InvalidArgumentException('Missing option key `port`');
        }

        if (!isset($options['host'])) {
            throw new InvalidArgumentException('Missing option key `host`');
        }

        $socket = new Reactor($server['loop']);

        return new static(new Server($socket, $server['loop']), $socket, $server['monolog'], $options['host'], $options['port']);
    }
}
