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
    private $channel;
    private $conf;

    public function __construct(Configuration $conf)
    {
        parent::__construct('log:process');

        $this->conn = RabbitMQFactory::createConnection($conf);
        $this->channel = $this->conn->channel();
        $this->conf = $conf;
        $this->setDescription('Process log queue');

        $this->addOption('iterations', 'i', InputOption::VALUE_OPTIONAL, 'The number of iterations', true);

        return $this;
    }

    public function doExecute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Processing log messages ...");

        $iterations = $input->getOption('iterations')? : true;

        $worker = new LogWorker($this->container['dm'], $this->channel, $this->container['monolog']);
        $worker->run($iterations);
    }
}