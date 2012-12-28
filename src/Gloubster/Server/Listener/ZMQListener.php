<?php

namespace Gloubster\Server\Listener;

use Gloubster\Exception\InvalidArgumentException;
use Gloubster\Server\GloubsterServerInterface;
use Monolog\Logger;
use React\EventLoop\LoopInterface;
use React\ZMQ\Context;

class ZMQListener implements JobListenerInterface
{
    private $context;
    private $pull;

    public function __construct(Context $context, array $configuration)
    {
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

        $this->pull = $this->context->getSocket(\ZMQ::SOCKET_PULL, null);
        $this->pull->bind(sprintf('%s://%s:%s', $configuration['transport'], $configuration['address'], $configuration['port']));
    }

    /**
     * {@inheritdoc}
     */
    public function attach(GloubsterServerInterface $server)
    {
        $this->pull->on('error', array($server, 'incomingError'));
        $this->pull->on('message', array($server, 'incomingMessage'));
    }

    /**
     * {@inheritdoc}
     */
    public static function create(LoopInterface $loop, Logger $logger, array $options)
    {
        return new static(new Context($loop), $options);
    }
}
