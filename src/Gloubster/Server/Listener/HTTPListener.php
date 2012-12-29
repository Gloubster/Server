<?php

namespace Gloubster\Server\Listener;

use Gloubster\Server\GloubsterServerInterface;
use Gloubster\Server\GloubsterServer;
use Gloubster\Exception\InvalidArgumentException;
use Gloubster\Exception\RuntimeException;
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
    public static function create(GloubsterServer $server, array $options)
    {
        if (!isset($options['port'])) {
            throw new InvalidArgumentException('Missing option key `port`');
        }

        if (!isset($options['host'])) {
            throw new InvalidArgumentException('Missing option key `host`');
        }

        try {
            $socket = new Reactor($server['loop']);
            $socket->listen($options['port'], $options['host']);
        } catch (\Exception $e) {
            throw new RuntimeException(sprintf('Unable to listen to %s:%s', $options['host'], $options['port']));
        }

        $server['monolog']->addInfo(sprintf('Listening for message on HTTP %s:%s', $options['host'], $options['port']));

        $server['dispatcher']->on('stop', function () use ($socket) {
            $socket->shutdown();
        });

        return new static(new Server($socket, $server['loop']));
    }
}
