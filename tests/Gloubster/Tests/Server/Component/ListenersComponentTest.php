<?php

namespace Gloubster\Tests\Server\Component;

use Evenement\EventEmitter;
use Gloubster\Exception\RuntimeException;
use Gloubster\Server\GloubsterServer;
use Gloubster\Server\GloubsterServerInterface;
use Gloubster\Server\Component\ListenersComponent;
use Gloubster\Server\Listener\JobListenerInterface;
use Gloubster\Tests\GloubsterTest;

/**
 * @covers Gloubster\Server\Component\ListenersComponent
 */
class ListenersComponentTest extends GloubsterTest
{
    /** @test */
    public function itShouldRegister()
    {
        $server = $this->getServer();

        $server['monolog']->expects($this->never())
            ->method('addError');

        $server['configuration']['listeners'] = array(
            array(
                'type'    => __NAMESPACE__ . '\\ListenerTester',
                'options' => array(),
            ),
        );

        $server['test-token'] = false;
        $server->register(new ListenersComponent());

        $this->assertFalse($server['test-token']);
        $server['dispatcher']->emit('booted', array($server, $server['stomp-client']));
        $this->assertTrue($server['test-token']);
    }

    /**
     * @test
     */
    public function itShouldFailWhenRegisteringInvalidClassnames()
    {
        $server = $this->getServer();

        $server['monolog']->expects($this->once())
            ->method('addError');

        $server['configuration']['listeners'] = array(
            array(
                'type'    => 'InvalidNamespace\\Listener',
                'options' => array(),
            ),
        );

        $server->register(new ListenersComponent());
        $server['dispatcher']->emit('stomp-connected', array($server, $server['stomp-client']));
    }

    /** @test */
    public function itShouldFailWhenRegisteringInvalidListener()
    {
        $server = $this->getServer();

        $server['monolog']->expects($this->once())
            ->method('addError');

        $server['configuration']['listeners'] = array(
            array(
                'type'    => 'Gloubster\\Configuration',
                'options' => array(),
            ),
        );

        $server->register(new ListenersComponent());
        $server['dispatcher']->emit('stomp-connected', array($server, $server['stomp-client']));
    }

    /** @test */
    public function itShouldLogErrorIfListenerBuildFails()
    {
        $server = $this->getServer();

        $server['monolog']->expects($this->once())
            ->method('addError');

        $server['configuration']['listeners'] = array(
            array(
                'type'    => __NAMESPACE__ . '\\ListenerFailTester',
                'options' => array(),
            ),
        );

        $server->register(new ListenersComponent());

        // this attach listeners to the stomp server
        $server['dispatcher']->emit('stomp-connected', array($server, $server['stomp-client']));
    }

    /** @test */
    public function aDefualtConfigShouldHandleAllEventsWithoutProblems()
    {
        $server = $this->getServer();

        $component = new ListenersComponent();
        $component->register($server);

        $server['dispatcher']->emit('redis-connected', array($server, $this->getPredisAsyncClient(), $this->getPredisAsyncConnection()));
        $server['dispatcher']->emit('stomp-connected', array($server, $server['stomp-client']));
        $server['dispatcher']->emit('booted', array($server));
    }
}

class ListenerTester extends EventEmitter implements JobListenerInterface
{
    private $server;
    public function __construct($server)
    {
        $this->server = $server;
    }
    public function listen()
    {
        $this->server['test-token'] = true;
    }

    public function shutdown()
    {
        $this->server['test-token'] = false;
    }

    public static function create(GloubsterServerInterface $server, array $options)
    {
        $server['created'] = $options;
        return new static($server);
    }
}

class ListenerFailTester extends EventEmitter implements JobListenerInterface
{
    public function listen()
    {
    }

    public function shutdown()
    {
    }

    public static function create(GloubsterServerInterface $server, array $options)
    {
        throw new RuntimeException('fails for test');
    }
}
