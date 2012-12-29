<?php

namespace Gloubster\Server;

use Gloubster\Configuration;
use Gloubster\Server\Component\ComponentInterface;
use Monolog\Logger;
use React\EventLoop\LoopInterface;

interface GloubsterServerInterface extends \ArrayAccess
{
    /**
     * Register a server component
     *
     * @param ComponentInterface $component
     */
    public function register(ComponentInterface $component);

    /**
     * Runs the server
     */
    public function run();

    /**
     * Stops the server
     */
    public function stop();

    /**
     * One of the listeners had received a message
     *
     * @param type $message
     */
    public function incomingMessage($message);

    /**
     * One of the listeners had received a wrong message
     *
     * @param \Exception $error
     */
    public function incomingError(\Exception $error);

    /**
     * GloubsterServer builder
     *
     * @param LoopInterface $loop The event loop object
     * @param Configuration $conf The server configuration
     * @param Logger $logger      A logger
     *
     * @return GloubsterServerInterface
     */
    public static function create(LoopInterface $loop, Configuration $conf, Logger $logger);
}
