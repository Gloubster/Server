<?php

namespace Gloubster\Server\Console;

use Gloubster\Server\Console\AbstractCommand;
use Gloubster\Server\Worker\LogWorker;
use PhpAmqpLib\Channel\AMQPChannel;
use Gloubster\Configuration;
use Gloubster\Worker\Factory;
use Monolog\Logger;
use Gloubster\RabbitMQFactory;
use Neutron\TemporaryFilesystem\TemporaryFilesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class LogProcess extends AbstractCommand
{
    private $conn;
    private $conf;

    public function __construct(Configuration $conf)
    {
        parent::__construct('log:process');

        $this->conf = $conf;

        $this->setDescription('Process log queue')
             ->addOption('iterations', 'i', InputOption::VALUE_OPTIONAL, 'The number of iterations', true);

        return $this;
    }

    public function doExecute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Establishing connection ...");

        $this->conn = RabbitMQFactory::createConnection($this->conf);

        $output->writeln("Processing log messages ...");

        $worker = new LogWorker($this->container['dm'], $this->conn->channel(), $this->container['monolog']);
        $worker->run($input->getOption('iterations')? : true);
    }
}
