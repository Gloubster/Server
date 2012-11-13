<?php

namespace Gloubster\Server\Console;

use Gloubster\Server\Console\AbstractCommand;
use Gloubster\Server\Worker\LogWorker;
use PhpAmqpLib\Channel\AMQPChannel;
use Gloubster\Configuration;
use Gloubster\Worker\Factory;
use Monolog\Logger;
use Neutron\TemporaryFilesystem\TemporaryFilesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class LogProcess extends AbstractCommand
{
    private $logger;
    private $channel;
    private $conf;

    public function __construct(AMQPChannel $channel, Configuration $conf)
    {
        parent::__construct('log:process');

        $this->channel = $channel;
        $this->conf = $conf;
        $this->setDescription('Process log queue');

        $this->addOption('iterations', 'i', InputOption::VALUE_OPTIONAL, 'The number of iterations', true);

        return $this;
    }

    public function doExecute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Processing log messages ...");

        $queue = $this->conf['logs']['queue-name'];

        if (defined($queue)) {
            $queue = constant($queue);
        }

        $iterations = $input->getOption('iterations')? : true;

        $worker = new LogWorker($this->container['dm'], $this->channel, $queue, $this->container['monolog']);
        $worker->run($iterations);
    }
}