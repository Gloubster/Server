#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';


$Gloubster = require __DIR__ . '/../src/Gloubster/App.php';

$helpers = array(
  'dm' => new Doctrine\ODM\MongoDB\Tools\Console\Helper\DocumentManagerHelper($Gloubster['doctrine.odm.mongodb.dm']),
);

$cli = new \Symfony\Component\Console\Application('Doctrine ODM MongoDB Command Line Interface', Doctrine\ODM\MongoDB\Version::VERSION);

$cli->setCatchExceptions(true);

$helperSet = $cli->getHelperSet();
foreach ($helpers as $name => $helper)
{
  $helperSet->set($helper, $name);
}

$cli->addCommands(array(
  new \Doctrine\ODM\MongoDB\Tools\Console\Command\Schema\CreateCommand(),
  new \Doctrine\ODM\MongoDB\Tools\Console\Command\Schema\DropCommand(),
  new \Doctrine\ODM\MongoDB\Tools\Console\Command\ClearCache\MetadataCommand(),
  new \Doctrine\ODM\MongoDB\Tools\Console\Command\QueryCommand(),
  new \Doctrine\ODM\MongoDB\Tools\Console\Command\GenerateRepositoriesCommand,
  new \Doctrine\ODM\MongoDB\Tools\Console\Command\GenerateProxiesCommand,
  new \Doctrine\ODM\MongoDB\Tools\Console\Command\GenerateHydratorsCommand,
  new \Doctrine\ODM\MongoDB\Tools\Console\Command\GenerateDocumentsCommand,
));
$cli->run();