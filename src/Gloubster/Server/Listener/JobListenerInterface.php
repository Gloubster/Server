<?php

namespace Gloubster\Server\Listener;

use Evenement\EventEmitterInterface;
use Gloubster\Server\GloubsterServerInterface;

/**
 * Gloubster Job listeners listen for Job is their on implementation.
 */
interface JobListenerInterface extends EventEmitterInterface
{
    public function listen();
    public function shutdown();

    /**
     * Public method to create the listener
     *
     * @param GloubsterServer  $server  The gloubster server
     * @param array            $options An array of options to build the listener
     *
     * @return JobListenerInterface The new listener
     */
    public static function create(GloubsterServerInterface $server, array $options);
}
