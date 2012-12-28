<?php

namespace Gloubster\Server\Console;

use Gloubster\Server\Console\AbstractCommand;
use Gloubster\Configuration;
use Gloubster\Server\GloubsterServer;
use Gloubster\Server\Component\ListenersComponent;
use Gloubster\Server\Component\LogBuilderComponent;
use Gloubster\Server\Component\RabbitMQMonitorComponent;
use Gloubster\Server\Component\ServerMonitorComponent;
use Gloubster\Server\Component\StopComponent;
use Gloubster\Server\Component\WorkerMonitorBroadcastComponent;
use React\EventLoop\Factory as LoopFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunServer extends AbstractCommand
{
    private $conf;
    private $guaranteeManager;

    public function __construct(Configuration $conf)
    {
        parent::__construct('server:run');

        $this->conf = $conf;

        $this->setDescription('Run gloubster server');
        $this->addOption('test', 't', InputOption::VALUE_NONE, 'start and stop the server');

        return $this;
    }

    public function doExecute(InputInterface $input, OutputInterface $output)
    {
        $loop = LoopFactory::create();

        $server = GloubsterServer::create($loop, $this->conf, $this->container['monolog']);

        $server->register(new ListenersComponent());
        $server->register(new LogBuilderComponent());
        $server->register(new RabbitMQMonitorComponent());
        $server->register(new ServerMonitorComponent());
        $server->register(new WorkerMonitorBroadcastComponent());

        if ($input->getOption('test')) {
            $server->register(new StopComponent());
        }

        $server->run();
    }
}
