<?php

namespace Gloubster;

require_once __DIR__ . '/../../vendor/autoload.php';

use Knp\Silex\ServiceProvider\DoctrineMongoDBServiceProvider;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;

$app = new Application();

$app['debug'] = true;

$app->register(new TwigServiceProvider(), array(
    'twig.path'    => __DIR__ . '/../../views',
    'twig.options' => array(
        'cache' => __DIR__ . '/../../cache/',
    ),
));

$app->register(new DoctrineMongoDBServiceProvider(), array(
    'doctrine.odm.mongodb.connection_options' => array(
        'database'                       => 'gloubster',
        'host'                           => 'localhost',
    ),
    'doctrine.odm.mongodb.documents' => array(
        array(
            'type'                                       => 'yml',
            'path'                                       => __DIR__ . '/../../ressource/doctrine/documents',
            'namespace'                                  => 'Gloubster\\Documents'
        ),
    ),
    'doctrine.odm.mongodb.proxies_dir'           => __DIR__ . '/../../cache/doctrine/odm/mongodb/Proxy',
    'doctrine.odm.mongodb.auto_generate_proxies' => true,
    'doctrine.odm.mongodb.hydrators_dir'         => __DIR__ . '/../../cache/doctrine/odm/mongodb/Hydrator',
    'doctrine.odm.mongodb.metadata_cache'        => 'array',
));

$app->get('/', function() use ($app) {

        $repository = $app['doctrine.odm.mongodb.dm']->getRepository('Gloubster\\Documents\\JobSet');

        $jobsets = $repository->findAll();

        return $app['twig']->render('index.html.twig', array('jobsets' => $jobsets));
    });

return $app;
