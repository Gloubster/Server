<?php

namespace Gloubster\Server\Component;

use Gloubster\Server\GloubsterServerInterface;

class StopComponent implements ComponentInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(GloubsterServerInterface $server)
    {
        $server['dispatcher']->on('booted', function ($server) {
            $server['monolog']->addInfo('StopComponent is now stopping the server, shutting down...');
            $server->stop();
        });
    }
}
