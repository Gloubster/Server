<?php

namespace Gloubster;

use Silex\Application;

$app = new Application();

$app->register(new \Knp\Silex\ServiceProvider\DoctrineMongoDBServiceProvider(),array(
    'doctrine.odm.mongodb.connection_options' => array(
        'database' => 'gloubster',
        'host'     => 'localhost',
    ),
    'doctrine.odm.mongodb.documents' => array(
        array(
            'type' => 'yml',
            'path' => __DIR__ . '/../../ressource/doctrine/documents',
            'namespace' => 'Gloubster\\Documents'
            ),
    ),
));

return $app;
