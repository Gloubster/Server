<?php

use Gloubster\Server\Console\ServerEnsureConfiguration;
use Gloubster\CLI;
use Gloubster\Tests\GloubsterTest;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @covers Gloubster\Server\Console\ServerEnsureConfiguration
 */
class ServerEnsureConfigurationTest extends GloubsterTest
{
    public function testExecute()
    {
        $conf = $this->getTestConfiguration();
        $this->getSessionServer($conf);

        $application = new CLI('Gloubster');
        $application->command(new ServerEnsureConfiguration($conf));

        $logger = $this->getLogger();

        $logger->expects($this->never())
            ->method('addError');

        $logger->expects($this->never())
            ->method('addInfo');

        $application['monolog'] = $logger;

        $command = $application['console']->find('server:ensure-configuration');

        $commandTester = new CommandTester($command);
        $commandTester->execute(array('command' => $command->getName(), '--dry-run' => true));

        $this->assertGreaterThanOrEqual(0, strpos($commandTester->getDisplay(), 'Running command as dry-run'));
    }
}
