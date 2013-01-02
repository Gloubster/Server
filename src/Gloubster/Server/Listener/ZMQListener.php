<?php

namespace Gloubster\Server\Listener;

use Gloubster\Exception\InvalidArgumentException;
use Gloubster\Server\GloubsterServerInterface;
use Gloubster\Server\MessageHandler;
use Monolog\Logger;
use React\ZMQ\Context;

class ZMQListener implements JobListenerInterface
{
    private $conf;
    private $context;
    private $logger;
    private $pull;
    private $bound = false;

    public function __construct(Context $context, Logger $logger, array $configuration)
    {
        $this->conf = $configuration;
        $this->logger = $logger;

        if (!isset($configuration['transport'])) {
            throw new InvalidArgumentException('Missing configuration key `transport`');
        }

        if (!isset($configuration['address'])) {
            throw new InvalidArgumentException('Missing configuration key `address`');
        }

        if (!isset($configuration['port'])) {
            throw new InvalidArgumentException('Missing configuration key `port`');
        }

        $this->context = $context;
        $this->pull = $this->context->getSocket(\ZMQ::SOCKET_REP, null);
    }

    /**
     * {@inheritdoc}
     */
    public function attach(MessageHandler $handler)
    {
        $pull = $this->pull;
        $this->pull->on('error', function ($error) use ($handler) {
            $handler->error($error);
        });
        $this->pull->on('message', function ($message) use ($pull, $handler) {
            $handler->receive($message);
            $pull->send('{}');
        });
    }

    public function __destruct()
    {
        if ($this->bound) {
            $this->shutdown();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function listen()
    {
        $this->pull->bind(sprintf('%s://%s:%s', $this->conf['transport'], $this->conf['address'], $this->conf['port']));
        $this->bound = true;
        $this->logger->addInfo(sprintf('Listening for message on ZMQ protocol %s://%s:%s', $this->conf['transport'], $this->conf['address'], $this->conf['port']));
    }

    /**
     * {@inheritdoc}
     */
    public function shutdown()
    {
        $this->pull->unbind(sprintf('%s://%s:%s', $this->conf['transport'], $this->conf['address'], $this->conf['port']));
        $this->bound = false;
        $this->logger->addInfo(sprintf('Stops Listening on ZMQ protocol %s://%s:%s', $this->conf['transport'], $this->conf['address'], $this->conf['port']));
    }

    /**
     * {@inheritdoc}
     */
    public static function create(GloubsterServerInterface $server, array $options)
    {
        return new static(new Context($server['loop']), $server['monolog'], $options);
    }
}
