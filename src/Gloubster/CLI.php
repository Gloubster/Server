<?php

namespace Gloubster;

use Symfony\Component\HttpFoundation\Request;
use Gloubster\Server\Console\AbstractCommand;
use Symfony\Component\Console\Shell;
use Symfony\Component\Console\Application as ConsoleApplication;

/**
 * Gloubster Command Line Application
 *
 * Largely inspired by Cilex
 * @see https://github.com/Cilex/Cilex
 */
class CLI extends WebApplication
{

    /**
     * Registers the autoloader and necessary components.
     *
     * @param string      $name    Name for this application.
     * @param string|null $version Version number for this application.
     */
    public function __construct($name, $version = null, $environment = null)
    {
        parent::__construct($environment);

        $this['session.test'] = true;

        $this['console'] = $this->share(function () use ($name, $version) {
            return new ConsoleApplication($name, $version);
        });
    }

    /**
     * Executes this application.
     *
     * @param bool $interactive runs in an interactive shell if true.
     */
    public function runCLI($interactive = false)
    {
        $app = $this['console'];
        if ($interactive) {
            $app = new Shell($app);
        }

        $app->run();
    }

    public function run(Request $request = null)
    {
        $this->runCLI();
    }

    /**
     * Adds a command object.
     *
     * If a command with the same name already exists, it will be overridden.
     *
     * @param AbstractCommand $command A Command object
     */
    public function command(AbstractCommand $command)
    {
        $command->setContainer($this);
        $this['console']->add($command);
    }
}
