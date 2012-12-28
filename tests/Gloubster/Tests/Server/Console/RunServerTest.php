<?php

use Gloubster\Configuration;
use Gloubster\Server\Console\RunServer;
use Gloubster\CLI;
use Symfony\Component\Console\Tester\CommandTester;

class RunServerTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $application = new CLI('Gloubster');
        $application->command(new RunServer(new Configuration(file_get_contents(__DIR__ . '/../../../../resources/config.json'))));

        $logger = $this->getMockBuilder('Monolog\\Logger')
                    ->disableOriginalConstructor()
                    ->getMock();

        $logger->expects($this->never())
            ->method('addError');

        $logger->expects($this->atLeastOnce())
            ->method('addInfo');

        $application['monolog'] = $logger;

        $command = $application['console']->find('server:run');

        $commandTester = new CommandTester($command);
        $commandTester->execute(array('command' => $command->getName(), '--test' => true));

        $this->assertEquals('', $commandTester->getDisplay());
    }
}
