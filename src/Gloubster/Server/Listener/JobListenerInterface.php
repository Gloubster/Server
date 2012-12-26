<?php

namespace Gloubster\Server\Listener;

use Gloubster\Server\GloubsterServerInterface;
use React\EventLoop\LoopInterface;

/**
 * Gloubster Job listeners listen for Job is their on implementation.
 */
interface JobListenerInterface
{
    /**
     * Attach the listener to a server. The listener can be bound to only one
     * server at the same time.
     *
     * @param GloubsterServerInterface $server
     *
     * @return JobListenerInterface The listener
     */
    public function attach(GloubsterServerInterface $server);

    /**
     * Public method to create the listener
     *
     * @param LoopInterface  $loop A Event LoopInterface
     * @param array $options An array of options to build the listener
     *
     * @return JobListenerInterface The new listener
     */
    public static function create(LoopInterface $loop, array $options);
}
