<?php

namespace Gloubster\Client;

use Monolog\Logger;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunClient extends Command
{

    public function __contruct($name = null)
    {
        parent::__construct($name);
        $this->setDescription('Runs Gloubster gearman client');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $app = require __DIR__ . '/../App.php';

        $logger = new Logger('Gearman Client');
        
        if ($input->getOption('verbose')) {
            $logger->pushHandler(new StreamHandler('php://stdout'));
        } else {
            $logger->pushHandler(new NullHandler());
        }

        $client = new Client(new \GearmanClient(), $app['configuration'], $app['dm'], $logger);

        foreach ($app['configuration']['gearman-servers'] as $server) {
            $client->addServer($server['host'], $server['port']);
        }

        $client->run();
    }
}
