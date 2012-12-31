<?php

namespace Gloubster\Server\Component;

use Gloubster\Server\GloubsterServerInterface;
use React\Stomp\Client;
use React\Curry\Util as Curry;
use React\Stomp\Factory as StompFactory;

class STOMPComponent implements ComponentInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(GloubsterServerInterface $server)
    {
        $server['stomp-client.started'] = false;

        $factory = new StompFactory($server['loop']);
        $server['stomp-client'] = $factory->createClient(array(
            'host'     => $server['configuration']['server']['host'],
            'port'     => $server['configuration']['server']['stomp-gateway']['port'],
            'user'     => $server['configuration']['server']['user'],
            'passcode' => $server['configuration']['server']['password'],
            'vhost'    => $server['configuration']['server']['vhost'],
        ));

        $server['dispatcher']->on('stop', function ($server) {
            $server['stomp-client']->disconnect();
            $server['stomp-client.started'] = false;
            $server['monolog']->addInfo('STOMP Server shutdown');
        });

        $component = $this;
        $server['dispatcher']->on('start', function ($server) use ($component) {

            $server['stomp-client']->on('error', array($server, 'logError'));

            $server['stomp-client']
                ->connect()
                ->then(
                    Curry::bind(array($component, 'activateService'), $server),
                    Curry::bind(array($server, 'throwError'))
                );
            $server['monolog']->addInfo('Connecting to STOMP Gateway...');
        });
    }

    public function activateService(GloubsterServerInterface $server, Client $stomp)
    {
        $server['monolog']->addInfo('Connected to STOMP Gateway !');
        $server['dispatcher']->emit('stomp-connected', array($server, $stomp));
        $server['stomp-client.started'] = true;
        $server->probeAllSystems();
    }
}
