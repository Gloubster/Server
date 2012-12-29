<?php

namespace Gloubster\Tests\Server\Component;

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

        $server['configuration'] = $this->getTestConfiguration();
        $server['configuration']['listeners'] = array(
            array(
                'type'    => __NAMESPACE__ . '\\ListenerTester',
                'options' => array(),
            ),
        );

        $server['test-token'] = false;
        $server->register(new ListenersComponent());

        $this->assertFalse($server['test-token']);
        $server['dispatcher']->emit('stomp-connected', array($server, $server['stomp-client']));
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

        $server['configuration'] = $this->getTestConfiguration();
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

        $server['configuration'] = $this->getTestConfiguration();
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

        $server['configuration'] = $this->getTestConfiguration();
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
        $server['configuration'] = $this->getTestConfiguration();

        $component = new ListenersComponent();
        $component->register($server);

        $server['dispatcher']->emit('redis-connected', array($server, $this->getPredisAsyncClient(), $this->getPredisAsyncConnection()));
        $server['dispatcher']->emit('stomp-connected', array($server, $server['stomp-client']));
        $server['dispatcher']->emit('boot-connected', array($server));
    }
}

class ListenerTester implements JobListenerInterface
{
    public function attach(GloubsterServerInterface $server)
    {
        $server['test-token'] = true;
    }

    public static function create(GloubsterServer $server, array $options)
    {
        $server['created'] = $options;
        return new static();
    }
}

class ListenerFailTester implements JobListenerInterface
{
    public function attach(GloubsterServerInterface $server)
    {
    }

    public static function create(GloubsterServer $server, array $options)
    {
        throw new RuntimeException('fails for test');
    }
}
