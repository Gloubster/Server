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

//        $manager = new \Spork\ProcessManager(new \Spork\EventDispatcher\EventDispatcher());

        $i = 1;
//        for ($i = 1; $i <= 3; $i ++ ) {

            $logger = new Logger('Client-'.$i);

//            $logger->pushHandler(new \Monolog\Handler\RotatingFileHandler(__DIR__ . '/../../../logs/client-'.$i.'.log'));

            if ($input->getOption('verbose')) {
//                $logger->pushHandler(new StreamHandler('php://stdout'));
            } else {
                $logger->pushHandler(new NullHandler());
            }

            $output->write("Launching Client <info>$i</info> ...");

            $client = new Client(new \GearmanClient(), $app['configuration'], $app['dm'], $logger);


//            $manager->fork(function() use ($client) {
                $client->run();
//            });

            $output->writeln("Success !");

//        }
    }
}
