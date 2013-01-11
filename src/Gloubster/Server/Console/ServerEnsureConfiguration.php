<?php

namespace Gloubster\Server\Console;

use Gloubster\Server\Console\AbstractCommand;
use Gloubster\Configuration;
use Gloubster\Configuration as RabbitMQConf;
use RabbitMQ\Management\APIClient;
use RabbitMQ\Management\Guarantee;
use RabbitMQ\Management\Entity\Binding as RabbitMQBinding;
use RabbitMQ\Management\Entity\Exchange as RabbitMQExchange;
use RabbitMQ\Management\Entity\Queue as RabbitMQQueue;
use RabbitMQ\Management\Exception\ExceptionInterface as RabbitMQManagementException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class ServerEnsureConfiguration extends AbstractCommand
{
    private $conf;
    private $guaranteeManager;

    public function __construct(Configuration $conf)
    {
        parent::__construct('server:ensure-configuration');

        $this->conf = $conf;

        $this->setDescription('Ensure that queues and echanges are declared and bounds')
             ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Dry run the command');

        return $this;
    }

    public function doExecute(InputInterface $input, OutputInterface $output)
    {
        $this->guaranteeManager = new Guarantee(APIClient::factory(array_merge($this->conf['server'], $this->conf['server']['server-management'])));

        $output->getFormatter()->setStyle('error', new OutputFormatterStyle('red'));
        $output->getFormatter()->setStyle('alert', new OutputFormatterStyle('yellow'));
        $output->getFormatter()->setStyle('ok', new OutputFormatterStyle('green'));
        $output->getFormatter()->setStyle('title', new OutputFormatterStyle('white', 'cyan', array('bold', 'blink')));

        $dry = $input->getOption('dry-run');
        $errors = 0;
        $changesRequired = false;

        $pendingAction = '...';

        if ($dry) {
            $pendingAction = '';
            $output->writeln("");
            $output->writeln("<alert>Running command as dry-run</alert>");
        }

        $queues = array(
            RabbitMQConf::QUEUE_ERRORS,
            RabbitMQConf::QUEUE_IMAGE_PROCESSING,
            RabbitMQConf::QUEUE_LOGS,
            RabbitMQConf::QUEUE_VIDEO_PROCESSING,
        );

        $output->writeln("");
        $output->writeln("<title> Queues configuration </title>");

        foreach ($queues as $name) {
            try {

                $queue = new RabbitMQQueue();
                $queue->vhost = $this->conf['server']['vhost'];
                $queue->name = $name;
                $queue->durable = true;
                $queue->auto_delete = false;

                $status = $this->guaranteeManager->probeQueue($queue);

                $expectedResult = '';
                switch ($status) {
                    case Guarantee::PROBE_ABSENT:
                        $output->write(" [ ] ");
                        $output->write(sprintf(" %s %s ", $name, $pendingAction));
                        $expectedResult = 'Added';
                        $changesRequired = true;
                        break;
                    case Guarantee::PROBE_MISCONFIGURED:
                        $output->write(" [<alert>-</alert>] ");
                        $output->write(sprintf(" %s %s ", $name, $pendingAction));
                        $expectedResult = 'Fixed';
                        $changesRequired = true;
                        break;
                    case Guarantee::PROBE_OK:
                        $output->write(" [<ok>X</ok>] ");
                        $output->write(sprintf(" %s ", $name));
                        break;
                }

                if (!$dry) {
                    try {
                        $this->guaranteeManager->ensureQueue($queue);
                        $output->write(sprintf("<ok>%s</ok>", $expectedResult));
                    } catch (RabbitMQManagementException $e) {
                        $errors++;
                        $output->write(sprintf("<error>failed</error> : %s", $e->getMessage()));
                    }
                }
            } catch (RabbitMQManagementException $e) {

            }

            $output->writeln("");
        }

        $output->writeln("");
        $output->writeln("<title> Exchanges configuration </title>");

        $exchanges = array(
            RabbitMQConf::EXCHANGE_DISPATCHER => 'direct',
            RabbitMQConf::EXCHANGE_MONITOR    => 'fanout',
        );

        foreach ($exchanges as $name => $type) {
            try {

                $exchange = new RabbitMQExchange();
                $exchange->vhost = $this->conf['server']['vhost'];
                $exchange->name = $name;
                $exchange->durable = true;
                $exchange->type = $type;

                $status = $this->guaranteeManager->probeExchange($exchange);

                $expectedResult = '';
                switch ($status) {
                    case Guarantee::PROBE_ABSENT:
                        $output->write(" [ ] ");
                        $output->write(sprintf(" %s %s ", $name, $pendingAction));
                        $expectedResult = 'Added';
                        $changesRequired = true;
                        break;
                    case Guarantee::PROBE_MISCONFIGURED:
                        $output->write(" [<alert>-</alert>] ");
                        $output->write(sprintf(" %s %s ", $name, $pendingAction));
                        $expectedResult = 'Fixed';
                        $changesRequired = true;
                        break;
                    case Guarantee::PROBE_OK:
                        $output->write(" [<ok>X</ok>] ");
                        $output->write(sprintf(" %s ", $name));
                        break;
                }

                if (!$dry) {
                    try {
                        $this->guaranteeManager->ensureExchange($exchange);
                        $output->write(sprintf("<ok>%s</ok>", $expectedResult));
                    } catch (RabbitMQManagementException $e) {
                        $errors++;
                        $output->write(sprintf("<error>failed</error> : %s", $e->getMessage()));
                    }
                }
            } catch (RabbitMQManagementException $e) {

            }

            $output->writeln("");
        }

        $output->writeln("");
        $output->writeln("<title> Routing configuration </title>");

        $routing_keys = array(
            RabbitMQConf::ROUTINGKEY_ERROR            => array(RabbitMQConf::EXCHANGE_DISPATCHER, RabbitMQConf::QUEUE_ERRORS),
            RabbitMQConf::ROUTINGKEY_IMAGE_PROCESSING => array(RabbitMQConf::EXCHANGE_DISPATCHER, RabbitMQConf::QUEUE_IMAGE_PROCESSING),
            RabbitMQConf::ROUTINGKEY_LOG              => array(RabbitMQConf::EXCHANGE_DISPATCHER, RabbitMQConf::QUEUE_LOGS),
            RabbitMQConf::ROUTINGKEY_VIDEO_PROCESSING => array(RabbitMQConf::EXCHANGE_DISPATCHER, RabbitMQConf::QUEUE_VIDEO_PROCESSING),
        );

        foreach ($routing_keys as $routing => $queueData) {
            try {

                list($exchangeName, $queueName) = $queueData;
                $binding = new RabbitMQBinding();
                $binding->routing_key = $routing;
                $binding->vhost = $this->conf['server']['vhost'];
                $binding->source = $exchangeName;
                $binding->destination = $queueName;
                $binding->routing_key = $binding->routing_key;

                $status = $this->guaranteeManager->probeBinding($binding);

                $expectedResult = '';
                switch ($status) {
                    case Guarantee::PROBE_ABSENT:
                        $output->write(" [ ] ");
                        $output->write(
                            sprintf(
                                " routing %s on %s => %s %s ",
                                $this->setToLength($routing, 22),
                                $exchangeName,
                                $queueName,
                                $pendingAction
                            )
                        );
                        $expectedResult = 'Added';
                        $changesRequired = true;
                        break;
                    case Guarantee::PROBE_MISCONFIGURED:
                        $output->write(" [<alert>-</alert>] ");
                        $output->write(
                            sprintf(
                                " routing %s on %s => %s %s ",
                                $this->setToLength($routing, 22),
                                $exchangeName,
                                $queueName,
                                $pendingAction
                            )
                        );
                        $expectedResult = 'Fixed';
                        $changesRequired = true;
                        break;
                    case Guarantee::PROBE_OK:
                        $output->write(" [<ok>X</ok>] ");
                        $output->write(
                            sprintf(
                                " routing %s on %s => %s ",
                                $this->setToLength($routing, 22),
                                $exchangeName,
                                $queueName
                            )
                        );
                        break;
                }

                if (!$dry) {
                    try {
                        $this->guaranteeManager->ensureBinding($binding);
                        $output->write(sprintf("<ok>%s</ok>", $expectedResult));
                    } catch (RabbitMQManagementException $e) {
                        $errors++;
                        $output->write(sprintf("<error>failed</error> : %s", $e->getMessage()));
                    }
                }
            } catch (RabbitMQManagementException $e) {

            }

            $output->writeln("");
        }

        $output->writeln("");

        if ($errors) {
            $output->writeln(sprintf("<error>%d error(s) occured</error>", $errors));
        } else {
            if ($dry && $changesRequired) {
                $output->writeln("<alert>It was just a dry-run, remove the option to apply changes</alert>");
            } else {
                $output->writeln("<ok>Everything seems OK !</ok>");
            }
        }

        $output->writeln("");
    }

    private function setToLength($string, $length)
    {
        while (strlen($string) < $length) {
            $string.= ' ';
        }

        return $string;
    }
}
