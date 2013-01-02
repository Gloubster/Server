<?php

namespace Gloubster\Server\Listener;

use Gloubster\Server\GloubsterServerInterface;
use Gloubster\Server\MessageHandler;

/**
 * Gloubster Job listeners listen for Job is their on implementation.
 */
interface JobListenerInterface
{
    /**
     * Triggers the start of listening
     */
    public function listen();

    /**
     * Attach the MessageHandler to the listener
     *
     * @param MessageHandler $handler
     */
    public function attach(MessageHandler $handler);

    /**
     * Triggers the end of listening
     */
    public function shutdown();

    /**
     * Public method to create the listener
     *
     * @param GloubsterServerInterface  $server  The gloubster server
     * @param array            $options An array of options to build the listener
     *
     * @return JobListenerInterface The new listener
     */
    public static function create(GloubsterServerInterface $server, array $options);
}
