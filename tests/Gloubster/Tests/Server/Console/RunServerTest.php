<?php

use Gloubster\Server\Console\RunServer;
use Gloubster\CLI;
use Gloubster\Tests\GloubsterTest;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @covers Gloubster\Server\Console\RunServer
 */
class RunServerTest extends GloubsterTest
{
    public function testExecute()
    {
        $application = new CLI('Gloubster');
        $application->command(new RunServer($this->getTestConfiguration()));

        $logger = $this->getLogger();

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
