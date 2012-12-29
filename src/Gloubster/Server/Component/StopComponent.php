<?php

namespace Gloubster\Server\Component;

use Gloubster\Server\GloubsterServer;

class StopComponent implements ComponentInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(GloubsterServer $server)
    {
        $server['dispatcher']->on('booted', function ($server) {
            $server['monolog']->addInfo('StopComponent is now stopping the server, shutting down...');
            $server->stop();
        });
    }
}
