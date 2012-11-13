<?php

namespace Gloubster\Server\Console;

use Gloubster\Server\Application;
use Monolog\Handler\StreamHandler;
use Symfony\Component\Console\Command\Command as SymfoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Abstract command taht represents a Gloubster base command
 */
abstract class AbstractCommand extends SymfoCommand
{
    /**
     * @var Application
     */
    protected $container = null;

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('verbose')) {
            $handler = new StreamHandler('php://stdout');
            $this->container['monolog']->pushHandler($handler);
        }

        return $this->doExecute($input, $output);
    }

    /**
     * Sets the application container containing all services.
     *
     * @param Application $container Application object to register.
     *
     * @return void
     */
    public function setContainer(Application $container)
    {
        $this->container = $container;
    }

    /**
     * Returns the application container.
     *
     * @return Application
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Returns a service contained in the application container or null if none
     * is found with that name.
     *
     * This is a convenience method used to retrieve an element from the
     * Application container without having to assign the results of the
     * getContainer() method in every call.
     *
     * @param string $name Name of the service
     *
     * @see self::getContainer()
     *
     * @return ServiceProvider
     */
    public function getService($name)
    {
        return isset($this->container[$name]) ? $this->container[$name] : null;
    }

    abstract public function doExecute(InputInterface $input, OutputInterface $output);
}
