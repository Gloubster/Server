<?php

namespace Gloubster\Server\Component;

use Gloubster\Server\GloubsterServerInterface;

interface ComponentInterface
{
    /**
     * Register the component in the provided GloubsterServer
     *
     * @param GloubsterServerInterface $server
     */
    public function register(GloubsterServerInterface $server);
}
