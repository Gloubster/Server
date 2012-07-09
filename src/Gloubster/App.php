<?php

namespace Gloubster;

require_once __DIR__ . '/../../vendor/autoload.php';

use Assetic\Asset\AssetCache;
use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Filter\LessFilter;
use Assetic\Filter\Yui\CssCompressorFilter;
use Assetic\Filter\Yui\JsCompressorFilter;
use Assetic\Cache\FilesystemCache;
use Gloubster\Application as GloubsterApp;
use Gloubster\Client\Configuration as ClientConfiguration;
use Silex\Application as SilexApplication;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use SilexExtension\AsseticExtension;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Knp\Silex\ServiceProvider\DoctrineMongoDBServiceProvider;

$app = new SilexApplication();

$app['debug'] = true;

$app['configuration'] = $app->share(function() {
        return new ClientConfiguration(file_get_contents(__DIR__ . '/../../config/config.json'));
    });

$app->register(new TwigServiceProvider(), array(
    'twig.path'    => __DIR__ . '/../../views',
    'twig.options' => array(
        'cache'               => __DIR__ . '/../../cache/',
        'strict_variables'    => true,
    ),
    'twig.form.templates' => array(
        'form_div_layout.html.twig',
        'common/form_div_layout.html.twig',
    ),
));

$app->register(new ValidatorServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new UrlGeneratorServiceProvider());
$app->register(new SessionServiceProvider());

$app->register(new DoctrineMongoDBServiceProvider(), array(
    'doctrine.odm.mongodb.connection_options' => array(
        'database'                       => 'gloubster',
        'host'                           => 'localhost',
    ),
    'doctrine.odm.mongodb.documents' => array(
        array(
            'type'                                       => 'yml',
            'path'                                       => __DIR__ . '/../../ressources/doctrine/documents',
            'namespace'                                  => 'Gloubster\\Documents'
        ),
    ),
    'doctrine.odm.mongodb.proxies_dir'           => __DIR__ . '/../../cache/doctrine/odm/mongodb/Proxy',
    'doctrine.odm.mongodb.auto_generate_proxies' => true,
    'doctrine.odm.mongodb.hydrators_dir'         => __DIR__ . '/../../cache/doctrine/odm/mongodb/Hydrator',
    'doctrine.odm.mongodb.metadata_cache'        => 'array',
));

$app->register(new TranslationServiceProvider(), array(
    'locale_fallback' => 'fr_FR',
));

$app['translator'] = $app->share($app->extend('translator', function($translator, $app) {
            $translator->addLoader('yaml', new YamlFileLoader());

            $translator->addResource('yaml', __DIR__ . '/../../locales/en_US.yml', 'en_US');
            $translator->addResource('yaml', __DIR__ . '/../../locales/fr_FR.yml', 'fr_FR');

            return $translator;
        }));

$app['locale'] = 'fr';

$app->register(new AsseticExtension(), array(
    'assetic.path_to_web' => __DIR__ . '/../../www/assets',
    'assetic.options'     => array(
        'debug'           => $app['debug'],
    ),
    'assetic.filters' => $app->protect(function($fm) {
            $fm->set('yui_css', new CssCompressorFilter(
                    '/usr/share/yui-compressor/yui-compressor.jar'
            ));
            $fm->set('yui_js', new JsCompressorFilter(
                    '/usr/share/yui-compressor/yui-compressor.jar'
            ));
        }),
    'assetic.assets' => $app->protect(function($am, $fm) {
            $am->set('base_css', new AssetCache(
                    new AssetCollection(array(
                        new FileAsset(
                            __DIR__ . '/../../vendor/twitter/bootstrap/less/bootstrap.less',
                            array(
                                new LessFilter(
                                    '/usr/local/bin/node', array('/usr/local/lib/node_modules')
                                ),
                                $fm->get('yui_css'))
                        ),
                        new FileAsset(__DIR__ . '/../../views/application.css', array($fm->get('yui_css'))),)
                    )
                    ,
                    new FilesystemCache(__DIR__ . '/../../cache/assetic')
            ));
            $am->set('modernizr', new AssetCache(
                    new FileAsset(
                        __DIR__ . '/../../ressources/assets/modernizr.2.5.3.js'
                        , array($fm->get('yui_js')))
                    ,
                    new FilesystemCache(__DIR__ . '/../../cache/assetic')
            ));
            $am->set('jquery', new AssetCache(
                    new FileAsset(
                        __DIR__ . '/../../ressources/assets/jquery-1.7.2.js'
                        , array($fm->get('yui_js')))
                    ,
                    new FilesystemCache(__DIR__ . '/../../cache/assetic')
            ));
            $am->set('bootstrap_js', new AssetCache(
                    new AssetCollection(array(
                        new FileAsset(__DIR__ . '/../../vendor/twitter/bootstrap/js/bootstrap-alert.js'),
                        new FileAsset(__DIR__ . '/../../vendor/twitter/bootstrap/js/bootstrap-button.js'),
                        new FileAsset(__DIR__ . '/../../vendor/twitter/bootstrap/js/bootstrap-collapse.js'),
                        new FileAsset(__DIR__ . '/../../vendor/twitter/bootstrap/js/bootstrap-dropdown.js'),
                        new FileAsset(__DIR__ . '/../../vendor/twitter/bootstrap/js/bootstrap-modal.js'),
                        new FileAsset(__DIR__ . '/../../vendor/twitter/bootstrap/js/bootstrap-tooltip.js'),
                        new FileAsset(__DIR__ . '/../../vendor/twitter/bootstrap/js/bootstrap-popover.js'),
                        new FileAsset(__DIR__ . '/../../vendor/twitter/bootstrap/js/bootstrap-scrollspy.js'),
                        new FileAsset(__DIR__ . '/../../vendor/twitter/bootstrap/js/bootstrap-tab.js'),
                        new FileAsset(__DIR__ . '/../../vendor/twitter/bootstrap/js/bootstrap-transition.js'),
                        new FileAsset(__DIR__ . '/../../vendor/twitter/bootstrap/js/bootstrap-typeahead.js'),
                        ), array($fm->get('yui_js')))
                    ,
                    new FilesystemCache(__DIR__ . '/../../cache/assetic')
            ));
            $am->set('bootstrap_img', new \Assetic\Asset\AssetCache(
                    new FileAsset(__DIR__ . '/../../vendor/twitter/bootstrap/img/glyphicons-halflings.png'),
                    new FilesystemCache(__DIR__ . '/../../cache/assetic')
            ));
            $am->get('base_css')->setTargetPath('css/styles.css');
            $am->get('bootstrap_img')->setTargetPath('img/glyphicons-halflings.png');
            $am->get('modernizr')->setTargetPath('js/modernizr.js');
            $am->get('jquery')->setTargetPath('js/jquery.js');
            $am->get('bootstrap_js')->setTargetPath('js/bootstrap.js');
        })
));

$app['dm'] = $app->share(function () use ($app) {
        return $app['doctrine.odm.mongodb.dm'];
    });

$app->mount('/', new GloubsterApp());

$app->mount('/api', new API());

return $app;
