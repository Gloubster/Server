<?php

namespace Gloubster\Server;

use Gloubster\Configuration;
use Gloubster\Exchange;
use Gloubster\Message\Job\JobInterface;
use Gloubster\Server\SessionHandler;
use Gloubster\RabbitMQ\Configuration as RabbitMQConf;
use Gloubster\Server\Component\ComponentInterface;
use Gloubster\Exception\RuntimeException;
use Gloubster\Message\Factory as MessageFactory;
use Monolog\Logger;
use Predis\Async\Client as PredisClient;
use Predis\Async\Connection\ConnectionInterface as PredisConnection;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Wamp\WampServer;
use Ratchet\Session\SessionProvider;
use React\Curry\Util as Curry;
use React\Stomp\Client;
use React\EventLoop\LoopInterface;
use React\Socket\Server as Reactor;
use React\Stomp\Factory as StompFactory;
use Evenement\EventEmitter;

/**
 * @event start
 * @event booted
 * @event stop
 * @event error
 * @event stomp-connected
 * @event redis-connected
 */
class GloubsterServer extends \Pimple implements GloubsterServerInterface
{
    private $components = array();

    public function __construct(WebsocketApplication $websocket, Client $client, LoopInterface $loop, Configuration $conf, Logger $logger)
    {
        $server = $this;

        $this['loop'] = $loop;
        $this['configuration'] = $conf;
        $this['monolog'] = $logger;
        $this['websocket-application'] = $websocket;
        $this['stomp-client'] = $client;
        $this['dispatcher'] = new EventEmitter();

        $this['stomp-client']->on('error', array($this, 'logError'));

        $redisErrorHandler = function (PredisClient $client, \Exception $e, PredisConnection $conn) use ($server) {
            call_user_func(array($server, 'logError'), $e);
        };

        $redisOptions = array(
            'on_error'  => $redisErrorHandler,
            'eventloop' => $server['loop'],
        );

        $this['dispatcher']->on('redis-connected', function () use ($server) {
            $server['redis.started'] = true;
            $server->probeAllSystems();
        });

        $this['dispatcher']->on('stomp-connected', function () use ($server) {
            $server['stomp-client.started'] = true;
            $server->probeAllSystems();
        });

        $this['dispatcher']->on('start', function () use ($server, $redisOptions) {
            $server['redis'] = new PredisClient(sprintf('tcp://%s:%s', $server['configuration']['redis-server']['host'], $server['configuration']['redis-server']['port']), $redisOptions);
            $server['redis']->connect(array($server, 'activateRedisServices'));
            $server['monolog']->addInfo('Connecting to Redis server...');
        });

        $this['websocket-application.socket'] = new Reactor($this['loop']);

        $this['dispatcher']->on('start', function () use ($server) {
            // Setup websocket server
            $server['websocket-application.socket']->listen($server['configuration']['websocket-server']['port'], $server['configuration']['websocket-server']['address']);
            $server['monolog']->addInfo(sprintf('Websocket Server listening on %s:%d', $server['configuration']['websocket-server']['address'], $server['configuration']['websocket-server']['port']));

            $server = new IoServer(new WsServer(
                           new SessionProvider(
                               new WampServer($server['websocket-application']),
                               SessionHandler::factory($server['configuration'])
                           )
                   ), $server['websocket-application.socket'], $server['loop']);
        });

        $this['dispatcher']->on('stop', function () use ($server) {
            $server['websocket-application.socket']->shutdown();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function register(ComponentInterface $component)
    {
        $component->register($this);
        $this->components[] = $component;

        $this['monolog']->addInfo(sprintf('Registering component %s', get_class($component)));
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this['monolog']->addInfo(sprintf('Starting server with %d components', count($this->components)));

        $this['dispatcher']->emit('start', array($this));

        $this['stomp-client']
            ->connect()
            ->then(
                Curry::bind(array($this, 'activateStompServices')),
                Curry::bind(array($this, 'throwError'))
            );
        $this['monolog']->addInfo('Connecting to STOMP Gateway...');

        $this['loop']->run();
    }

    public function stop()
    {
        $this['dispatcher']->emit('stop', array($this));

        // remove stop listeners

        $this['loop']->stop();
        $this['redis.started'] = $this['stomp-client.started'] = false;
    }

    public function activateRedisServices(PredisClient $client, PredisConnection $conn)
    {
        $this['monolog']->addInfo('Connected to Redis Server !');

        $this['dispatcher']->emit('redis-connected', array($this, $client, $conn));

        $this['redis.started'] = true;
        $this->probeAllSystems();
    }

    public function activateStompServices(Client $stomp)
    {
        $this['monolog']->addInfo('Connected to STOMP Gateway !');

        $this['dispatcher']->emit('stomp-connected', array($this, $stomp));

        $this['stomp-client.started'] = true;
        $this->probeAllSystems();
    }

    public function probeAllSystems()
    {
        if ($this['stomp-client.started'] && $this['redis.started']) {
            $this['monolog']->addInfo('All services loaded, server now running');
            $this['dispatcher']->emit('booted', array($this));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function incomingMessage($message)
    {
        $data = null;

        try {
            $data = MessageFactory::fromJson($message);
        } catch (RuntimeException $e) {
            $this['monolog']->addError(sprintf('Trying to sumbit a non-job message, got error %s with message %s', $e->getMessage(), $message));
            return;
        }

        if (!$data instanceof JobInterface) {
            $this['monolog']->addError(sprintf('Trying to sumbit a non-job message : %s', $message));
            return;
        }

        if (!$this['stomp-client']->isConnected()) {
            $this['monolog']->addError(sprintf('STOMP server not yet connected'));
            return;
        }

        $this['stomp-client']->send(sprintf('/exchange/%s', RabbitMQConf::EXCHANGE_DISPATCHER), $data->toJson());
    }

    /**
     * {@inheritdoc}
     */
    public function incomingError(\Exception $error)
    {
        $this->logError($error);
    }

    public function logError(\Exception $error)
    {
        $this['monolog']->addError($error->getMessage());
    }

    public function throwError(\Exception $error)
    {
        $this->logError($error);
        throw $error;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(LoopInterface $loop, Configuration $conf, Logger $logger)
    {
        $factory = new StompFactory($loop);
        $client = $factory->createClient(array(
            'host'     => $conf['server']['host'],
            'port'     => $conf['server']['stomp-gateway']['port'],
            'user'     => $conf['server']['user'],
            'passcode' => $conf['server']['password'],
            'vhost'    => $conf['server']['vhost'],
        ));

        $websocketApp = new WebsocketApplication($logger);

        return new GloubsterServer($websocketApp, $client, $loop, $conf, $logger);
    }
}
