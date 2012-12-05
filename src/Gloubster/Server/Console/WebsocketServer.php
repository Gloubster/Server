<?php

namespace Gloubster\Server\Console;

use Gloubster\Server\Console\AbstractCommand;
use Gloubster\Configuration;
use Gloubster\Exchange;
use Gloubster\Server\SessionHandler;
use Gloubster\Websocket\Application as WsApplication;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Wamp\WampServer;
use Ratchet\Session\SessionProvider;
use React\Curry\Util as Curry;
use React\EventLoop\Factory as LoopFactory;
use React\Socket\Server as Reactor;
use React\Stomp\Factory as StompFactory;
use React\Stomp\Protocol\Frame;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WebsocketServer extends AbstractCommand
{
    private $conn;
    private $channel;
    private $conf;

    public function __construct(Configuration $conf)
    {
        parent::__construct('server:websocket');

        $this->conf = $conf;
        $this->setDescription('Websocket server');

        return $this;
    }

    public function doExecute(InputInterface $input, OutputInterface $output)
    {
        $wsApplication = new WsApplication();
        $loop = LoopFactory::create();

        $socket = new Reactor($loop);
        $socket->listen($this->conf['websocket-server']['port'], $this->conf['websocket-server']['address']);

        $loop->addPeriodicTimer(500, Curry::bind(array($this, 'brodcastServerInformations'), $wsApplication));
        $loop->addPeriodicTimer(5000, Curry::bind(array($this, 'brodcastMQInformations'), $wsApplication));

        $factory = new StompFactory($loop);
        $client = $factory->createClient(array(
        ));

        $client->connect()
            ->then(
                Curry::bind(array($this, 'initializeMonitor'), $client, $wsApplication),
                Curry::bind(array($this, 'throwError'))
            );

        $server = new IoServer(new WsServer(
                       new SessionProvider(
                           new WampServer($wsApplication),
                           SessionHandler::factory($this->conf)
                       )
                   ), $socket, $loop);

        $loop->run();
    }

    public function brodcastServerInformations($wsApplication)
    {
        $wsApplication->onServerInformation(array(
             'memory' => memory_get_usage(),
        ));
    }

    public function throwError(\Exception $error)
    {
        throw $error;
    }

    public function brodcastMQInformations($wsApplication)
    {
        // todo
    }

    public function initializeMonitor($client, $wsApplication)
    {
        $client->subscribe(sprintf('/exchange/%s', Exchange::GLOUBSTER_MONITOR), function (Frame $frame) use ($wsApplication) {
                $wsApplication->onPresence(unserialize($frame->body));
            }
        );
    }
}
