<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\Silex\ServiceProvider;

use Silex\Application;
use Silex\ServiceProviderInterface;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\EventManager;
use Doctrine\MongoDB\Connection;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Driver\DriverChain;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use Doctrine\ODM\MongoDB\Mapping\Driver\XmlDriver;
use Doctrine\ODM\MongoDB\Mapping\Driver\YamlDriver;

/**
 * DoctrineMongoDBServiceProvider
 *
 * @author Justin Hileman <justin@justinhileman.info>
 */
class DoctrineMongoDBServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $this->setDoctrineMongoDBDefaults($app);
        $this->loadDoctrineMongoDBConfiguration($app);
        $this->loadDoctrineMongoDBConnection($app);
        $this->loadDoctrineMongoDBDocumentManager($app);

        foreach (array('Common', 'MongoDB', 'ODM\\MongoDB') as $vendor) {
            $key = sprintf('doctrine.%s.class_path', strtolower(str_replace('\\', '.', $vendor)));
            if (isset($app[$key])) {
                $app['autoloader']->registerNamespace(sprintf('Doctrine\\%s', $vendor), $app[$key]);
            }
        }
    }

    public function boot(Application $app)
    {
    }

    public function setDoctrineMongoDBDefaults(Application $app)
    {
        // default connection options
        $options = isset($app['doctrine.odm.mongodb.connection_options']) ? $app['doctrine.odm.mongodb.connection_options'] : array();
        $app['doctrine.odm.mongodb.connection_options'] = array_replace(array(
            'database' => null,
            'host'     => null,
        ), $options);

        // default extension options
        $defaults = array(
            'documents' => array(
                array('type' => 'annotation', 'path' => 'Document', 'namespace' => 'Document')
            ),
            'proxies_dir'           => 'cache/doctrine/odm/mongodb/Proxy',
            'proxies_namespace'     => 'DoctrineMongoDBProxy',
            'auto_generate_proxies' => true,
            'hydrators_dir'         => 'cache/doctrine/odm/mongodb/Hydrator',
            'hydrators_namespace'   => 'DoctrineMongoDBHydrator',
            'metadata_cache'        => 'apc',
        );

        foreach($defaults as $key => $value) {
            if (!isset($app['doctrine.odm.mongodb.'.$key])) {
                $app['doctrine.odm.mongodb.'.$key] = $value;
            }
        }
    }

    public function loadDoctrineMongoDBConfiguration(Application $app)
    {
        $app['doctrine.odm.mongodb.configuration'] = $app->share(function() use($app) {
            $config = new Configuration;

            if ($app['doctrine.odm.mongodb.metadata_cache'] == 'apc') {
                $cache = new ApcCache;
            } else {
                $cache = new ArrayCache;
            }
            $config->setMetadataCacheImpl($cache);

            if (isset($app['doctrine.odm.mongodb.connection_options']['database'])) {
                $config->setDefaultDB($app['doctrine.odm.mongodb.connection_options']['database']);
            }

            $chain = new DriverChain;
            foreach((array)$app['doctrine.odm.mongodb.documents'] as $document) {
                switch($document['type']) {
                    case 'annotation':
                        $reader = new AnnotationReader;
                        $driver = new AnnotationDriver($reader, (array)$document['path']);
                        $chain->addDriver($driver, $document['namespace']);
                        break;
                    case 'yml':
                        $driver = new YamlDriver((array)$document['path']);
                        $driver->setFileExtension('.yml');
                        $chain->addDriver($driver, $document['namespace']);
                        break;
                    case 'xml':
                        $driver = new XmlDriver((array)$document['path'], $document['namespace']);
                        $driver->setFileExtension('.xml');
                        $chain->addDriver($driver, $document['namespace']);
                        break;
                    default:
                        throw new \InvalidArgumentException(sprintf('"%s" is not a recognized driver', $document['type']));
                        break;
                }
            }
            $config->setMetadataDriverImpl($chain);

            $config->setProxyDir($app['doctrine.odm.mongodb.proxies_dir']);
            $config->setProxyNamespace($app['doctrine.odm.mongodb.proxies_namespace']);
            $config->setAutoGenerateProxyClasses($app['doctrine.odm.mongodb.auto_generate_proxies']);

            $config->setHydratorDir($app['doctrine.odm.mongodb.hydrators_dir']);
            $config->setHydratorNamespace($app['doctrine.odm.mongodb.hydrators_namespace']);

            return $config;
        });
    }

    public function loadDoctrineMongoDBConnection(Application $app)
    {
        $app['doctrine.mongodb.connection'] = $app->share(function () use ($app) {
            return new Connection($app['doctrine.odm.mongodb.connection_options']['host']);
        });
    }

    public function loadDoctrineMongoDBDocumentManager(Application $app)
    {
        $app['doctrine.odm.mongodb.event_manager'] = $app->share(function () use($app) {
            return new EventManager;
        });

        $app['doctrine.odm.mongodb.dm'] = $app->share(function () use($app) {
            return DocumentManager::create(
                $app['doctrine.mongodb.connection'],
                $app['doctrine.odm.mongodb.configuration'],
                $app['doctrine.odm.mongodb.event_manager']
            );
        });
    }
}
