<?php

namespace Gloubster;

require_once __DIR__ . '/../../vendor/autoload.php';

use Knp\Silex\ServiceProvider\DoctrineMongoDBServiceProvider;
use Silex\Application as SilexApplication;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\FormServiceProvider;

$app = new SilexApplication();

$app['debug'] = true;

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

$app->register(new \Silex\Provider\ValidatorServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new \Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new \Silex\Provider\SessionServiceProvider());

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



$app->register(new \Silex\Provider\TranslationServiceProvider(), array(
    'locale_fallback' => 'fr_FR',
));

$app['translator'] = $app->share($app->extend('translator', function($translator, $app) {
            $translator->addLoader('yaml', new \Symfony\Component\Translation\Loader\YamlFileLoader());

            $translator->addResource('yaml', __DIR__ . '/../../locales/en_US.yml', 'en_US');
            $translator->addResource('yaml', __DIR__ . '/../../locales/fr_FR.yml', 'fr_FR');

            return $translator;
        }));

$app['locale'] = 'fr';

$app->register(new \SilexExtension\AsseticExtension(), array(
    'assetic.path_to_web' => __DIR__ . '/../../www/assets',
    'assetic.options'     => array(
        'debug'           => $app['debug'],
    ),
    'assetic.filters' => $app->protect(function($fm) {
            $fm->set('yui_css', new \Assetic\Filter\Yui\CssCompressorFilter(
                    '/usr/share/yui-compressor/yui-compressor.jar'
            ));
            $fm->set('yui_js', new \Assetic\Filter\Yui\JsCompressorFilter(
                    '/usr/share/yui-compressor/yui-compressor.jar'
            ));
        }),
    'assetic.assets' => $app->protect(function($am, $fm) {
            $am->set('base_css', new \Assetic\Asset\AssetCache(
                    new \Assetic\Asset\AssetCollection(array(
                        new \Assetic\Asset\FileAsset(
                            __DIR__ . '/../../vendor/twitter/bootstrap/less/bootstrap.less',
                            array(
                                new \Assetic\Filter\LessFilter(
                                    '/usr/local/bin/node', array('/usr/local/lib/node_modules')
                                ),
                                $fm->get('yui_css'))
                        ),
                        new \Assetic\Asset\FileAsset(__DIR__ . '/../../views/application.css', array($fm->get('yui_css'))),)
                    )
                    ,
                    new \Assetic\Cache\FilesystemCache(__DIR__ . '/../../cache/assetic')
            ));
            $am->set('modernizr', new \Assetic\Asset\AssetCache(
                    new \Assetic\Asset\FileAsset(
                        __DIR__ . '/../../ressources/assets/modernizr.2.5.3.js'
                        , array($fm->get('yui_js')))
                    ,
                    new \Assetic\Cache\FilesystemCache(__DIR__ . '/../../cache/assetic')
            ));
            $am->set('jquery', new \Assetic\Asset\AssetCache(
                    new \Assetic\Asset\FileAsset(
                        __DIR__ . '/../../ressources/assets/jquery-1.7.2.js'
                        , array($fm->get('yui_js')))
                    ,
                    new \Assetic\Cache\FilesystemCache(__DIR__ . '/../../cache/assetic')
            ));
            $am->set('bootstrap_js', new \Assetic\Asset\AssetCache(
                    new \Assetic\Asset\AssetCollection(array(
                        new \Assetic\Asset\FileAsset(__DIR__ . '/../../vendor/twitter/bootstrap/js/bootstrap-alert.js'),
                        new \Assetic\Asset\FileAsset(__DIR__ . '/../../vendor/twitter/bootstrap/js/bootstrap-button.js'),
                        new \Assetic\Asset\FileAsset(__DIR__ . '/../../vendor/twitter/bootstrap/js/bootstrap-collapse.js'),
                        new \Assetic\Asset\FileAsset(__DIR__ . '/../../vendor/twitter/bootstrap/js/bootstrap-dropdown.js'),
                        new \Assetic\Asset\FileAsset(__DIR__ . '/../../vendor/twitter/bootstrap/js/bootstrap-modal.js'),
                        new \Assetic\Asset\FileAsset(__DIR__ . '/../../vendor/twitter/bootstrap/js/bootstrap-tooltip.js'),
                        new \Assetic\Asset\FileAsset(__DIR__ . '/../../vendor/twitter/bootstrap/js/bootstrap-popover.js'),
                        new \Assetic\Asset\FileAsset(__DIR__ . '/../../vendor/twitter/bootstrap/js/bootstrap-scrollspy.js'),
                        new \Assetic\Asset\FileAsset(__DIR__ . '/../../vendor/twitter/bootstrap/js/bootstrap-tab.js'),
                        new \Assetic\Asset\FileAsset(__DIR__ . '/../../vendor/twitter/bootstrap/js/bootstrap-transition.js'),
                        new \Assetic\Asset\FileAsset(__DIR__ . '/../../vendor/twitter/bootstrap/js/bootstrap-typeahead.js'),
                        ), array($fm->get('yui_js')))
                    ,
                    new \Assetic\Cache\FilesystemCache(__DIR__ . '/../../cache/assetic')
            ));
            $am->set('bootstrap_img', new \Assetic\Asset\AssetCache(
                    new \Assetic\Asset\FileAsset(__DIR__ . '/../../vendor/twitter/bootstrap/img/glyphicons-halflings.png')
                    ,
                    new \Assetic\Cache\FilesystemCache(__DIR__ . '/../../cache/assetic')
            ));
            $am->get('base_css')->setTargetPath('css/styles.css');
            $am->get('bootstrap_img')->setTargetPath('img/glyphicons-halflings.png');
            $am->get('modernizr')->setTargetPath('js/modernizr.js');
            $am->get('jquery')->setTargetPath('js/jquery.js');
            $am->get('bootstrap_js')->setTargetPath('js/bootstrap.js');
        })
));


$app['form.factory'] = $app->extend('form.factory', function($factory, $c){
    $factory->addType(new Form\Type\JobSetType());
    $factory->addType(new Form\Type\SpecificationType());

    return $factory;
});

$app['configuration'] = $app->share(function() {
    return new Client\Configuration(file_get_contents(__DIR__ . '/../../config/config.json'));
});

$app['dm'] = $app->share(function () use ($app) {
   return $app['doctrine.odm.mongodb.dm'];
});


$app->mount('/', new \Gloubster\Application());

$app->mount('/api', new API());

return $app;
