<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Gloubster\Server\Application;
use Symfony\Component\HttpFoundation\Request;

$app = new Application();

$app->get('/', function(Application $app, Request $request) {
    return $app['twig']->render('index.html.twig', array());
});

$app->run();
