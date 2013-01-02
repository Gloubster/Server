<?php

namespace Gloubster\Server\Component;

use Gloubster\Server\WebsocketApplication;
use Gloubster\Server\GloubsterServerInterface;
use Gloubster\Server\SessionHandler;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Wamp\WampServer;
use Ratchet\Session\SessionProvider;
use React\Socket\Server as Reactor;

class WebsocketServerComponent implements ComponentInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(GloubsterServerInterface $server)
    {
        $server['websocket-application.started'] = false;
        $server['websocket-application'] = new WebsocketApplication($server['monolog']);
        $server['websocket-application.socket'] = new Reactor($server['loop']);

        $server['dispatcher']->on('start', function ($server) {
            // Setup websocket server
            $server['websocket-application.socket']->listen($server['configuration']['websocket-server']['port'], $server['configuration']['websocket-server']['address']);
            $server['monolog']->addInfo(sprintf('Websocket Server listening on %s:%d', $server['configuration']['websocket-server']['address'], $server['configuration']['websocket-server']['port']));

            $server['websocket-server'] = new IoServer(new WsServer(
                           new SessionProvider(
                               new WampServer($server['websocket-application']),
                               SessionHandler::factory($server['configuration'])
                           )
                   ), $server['websocket-application.socket'], $server['loop']);

            $server['dispatcher']->emit('websocket-application-connected', array($server['websocket-application']));
            $server['websocket-application.started'] = true;
        });

        $server['dispatcher']->on('stop', function ($server) {
            $server['websocket-application.socket']->shutdown();
            $server['monolog']->addInfo('Websocket Server shutdown');
            $server['websocket-application.started'] = false;
        });
    }
}
