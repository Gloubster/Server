<?php

namespace Gloubster\Server\Component;

use Gloubster\Server\GloubsterServerInterface;
use Gloubster\Exception\RuntimeException;
use React\Stomp\Client;

class ListenersComponent implements ComponentInterface
{
    public $listeners = array();
    /**
     * {@inheritdoc}
     */
    public function register(GloubsterServerInterface $server)
    {
        foreach ($server['configuration']['listeners'] as $listenerConf) {
            $class_name = $listenerConf['type'];

            if (!class_exists($class_name)) {
                $server['monolog']->addError(sprintf('%s is not a valid classname', $class_name));
                continue;
            }

            if (!in_array('Gloubster\Server\Listener\JobListenerInterface', class_implements($class_name))) {
                $server['monolog']->addError(sprintf('%s is not implementing JobListenerInterface', $class_name));
                continue;
            }

            try {
                $listener = $class_name::create($server, $listenerConf['options']);
            } catch (RuntimeException $e) {
                $server['monolog']->addError(sprintf('Error while creating listener %s : %s', $listenerConf['type'], $e->getMessage()));
                continue;
            }

            $server['monolog']->addInfo(sprintf('Attaching listener %s', get_class($listener)));

            $listener->attach($server['message-handler']);
            $this->listeners[] = $listener;
        }

        $component = $this;

        $server['dispatcher']->on('booted', function (GloubsterServerInterface $server) use ($component) {
            foreach ($component->listeners as $listener) {
                $listener->listen();
            }
        });

        $server['dispatcher']->on('stop', function ($server) use ($component) {
            foreach ($component->listeners as $listener) {
                $listener->shutdown();
            }
        });
    }
}
