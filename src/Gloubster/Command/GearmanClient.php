<?php

namespace Gloubster\Command;

use Monolog\Logger;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GearmanClient extends Command
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
        if($input->getOption('verbose')) {
            $logger->pushHandler(new StreamHandler('php://stdout'));
        } else {
            $logger->pushHandler(new NullHandler());
        }

        $client = new \Gloubster\Gearman\Client($app['dm'], $logger);
        $client->run();
    }
}
