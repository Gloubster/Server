#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

$Gloubster = require __DIR__ . '/../src/Gloubster/App.php';

$cli = new \Symfony\Component\Console\Application('Gloubster CLI', '0');

$cli->addCommands(array(
    new Gloubster\Command\GearmanClient('gloubster:gearman:run-client'),
));
$cli->run();