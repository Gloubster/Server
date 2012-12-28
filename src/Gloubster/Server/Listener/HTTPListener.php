<?php

namespace Gloubster\Server\Listener;

use Gloubster\Server\GloubsterServerInterface;
use Gloubster\Exception\InvalidArgumentException;
use Monolog\Logger;
use React\EventLoop\LoopInterface;
use React\Http\Server;
use React\Socket\Server as Reactor;

class HTTPListener implements JobListenerInterface
{
    private $server;

    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    /**
     * {@inheritdoc}
     */
    public function attach(GloubsterServerInterface $server)
    {
        $this->server->on('request', function ($request, $response) use ($server) {
            $response->writeHead(200);
            $response->end();

            $data = new \stdClass();
            $data->message = '';

            $request->on('data', function ($chunk) use ($data) {
                $data->message .= $chunk;
            });

            $request->on('end', function() use ($data, $server) {
                call_user_func(array($server, 'incomingMessage'), $data->message);
            });

            $request->on('error', function ($error) use ($server) {
                call_user_func(array($server, 'incomingError'), $error);
            });
        });
    }

    /**
     * {@inheritdoc}
     */
    public static function create(LoopInterface $loop, Logger $logger, array $options)
    {
        if (!isset($options['port'])) {
            throw new InvalidArgumentException('Missing option key `port`');
        }

        if (!isset($options['host'])) {
            throw new InvalidArgumentException('Missing option key `host`');
        }

        $socket = new Reactor($loop);
        $socket->listen($options['port'], $options['host']);

        return new static(new Server($socket, $loop));
    }
}
