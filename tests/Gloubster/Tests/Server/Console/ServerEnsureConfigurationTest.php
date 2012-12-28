<?php

use Gloubster\Configuration;
use Gloubster\Server\Console\ServerEnsureConfiguration;
use Gloubster\CLI;
use Symfony\Component\Console\Tester\CommandTester;

class ServerEnsureConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $application = new CLI('Gloubster');
        $application->command(new ServerEnsureConfiguration(new Configuration(file_get_contents(__DIR__ . '/../../../../resources/config.json'))));

        $logger = $this->getMockBuilder('Monolog\\Logger')
                    ->disableOriginalConstructor()
                    ->getMock();

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
