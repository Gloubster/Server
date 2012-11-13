<?php

namespace Gloubster\Server;

use Silex\Application as SilexApplication;

//use Assetic\Asset\AssetCache;
//use Assetic\Asset\AssetCollection;
//use Assetic\Asset\FileAsset;
//use Assetic\Filter\LessFilter;
//use Assetic\Filter\Yui\CssCompressorFilter;
//use Assetic\Filter\Yui\JsCompressorFilter;
//use Assetic\Cache\FilesystemCache;
use Doctrine\Common\Cache\ArrayCache;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
//use Gloubster\Application as GloubsterApp;
//use Gloubster\Client\Configuration as ClientConfiguration;
//use Silex\Provider\FormServiceProvider;
//use Silex\Provider\SessionServiceProvider;
use Silex\Provider\MonologServiceProvider;
//use Silex\Provider\TranslationServiceProvider;
//use Silex\Provider\TwigServiceProvider;
//use Silex\Provider\UrlGeneratorServiceProvider;
//use Silex\Provider\ValidatorServiceProvider;
//use SilexExtension\AsseticExtension;
//use Symfony\Component\Translation\Loader\YamlFileLoader;
use Neutron\Silex\Provider\MongoDBODMServiceProvider;


class Application extends SilexApplication
{
    public function __construct()
    {
        $this['debug'] = true;

        //$this['configuration'] = $this->share(function() {
        //    return new ClientConfiguration(file_get_contents(__DIR__ . '/../../config/config.json'));
        //});
        //
        //$this->register(new TwigServiceProvider(), array(
        //    'twig.path'    => __DIR__ . '/../../../views',
        //    'twig.options' => array(
        //        'cache'               => __DIR__ . '/../../../cache/',
        //        'strict_variables'    => true,
        //    ),
        //    'twig.form.templates' => array(
        //        'form_div_layout.html.twig',
        //        'common/form_div_layout.html.twig',
        //    ),
        //));
        //
        $this->register(new MonologServiceProvider());

        $this['monolog.handler'] = function (Application $app) {
            return new NullHandler();
        };

        $this['monolog.level'] = function () {
            return Logger::DEBUG;
        };

        $this['monolog.name'] = 'myapp';

        //$this->register(new ValidatorServiceProvider());
        //$this->register(new FormServiceProvider());
        //$this->register(new UrlGeneratorServiceProvider());
        //$this->register(new SessionServiceProvider());

        $this->register(new MongoDBODMServiceProvider(), array(
            'doctrine.odm.mongodb.connection_options' => array(
                'database'                       => 'gloubster',
                'host'                           => 'localhost',
            ),
            'doctrine.odm.mongodb.documents' => array(
                array(
                    'type'                                       => 'yml',
                    'path'                                       => __DIR__ . '/../../../resources/doctrine/documents',
                    'namespace'                                  => 'Gloubster\\Documents'
                ),
            ),
            'doctrine.odm.mongodb.proxies_dir'           => __DIR__ . '/../../../cache/doctrine/odm/mongodb/Proxy',
            'doctrine.odm.mongodb.auto_generate_proxies' => true,
            'doctrine.odm.mongodb.hydrators_dir'         => __DIR__ . '/../../../cache/doctrine/odm/mongodb/Hydrator',
            'doctrine.odm.mongodb.metadata_cache'        => new ArrayCache(),
        ));
        //
        //$this->register(new TranslationServiceProvider(), array(
        //    'locale_fallback' => 'fr_FR',
        //));
        //
        //$this['translator'] = $this->share($this->extend('translator', function($translator, $app) {
        //    $translator->addLoader('yaml', new YamlFileLoader());
        //
        //    $translator->addResource('yaml', __DIR__ . '/../../locales/en_US.yml', 'en_US');
        //    $translator->addResource('yaml', __DIR__ . '/../../locales/fr_FR.yml', 'fr_FR');
        //
        //    return $translator;
        //}));
        //
        //$this['locale'] = 'fr';
        //
        //$this->register(new AsseticExtension(), array(
        //    'assetic.path_to_web' => __DIR__ . '/../../../www/assets',
        //    'assetic.options'     => array(
        //        'debug'           => $this['debug'],
        //    ),
        //    'assetic.filters' => $this->protect(function($fm) {
        //            $fm->set('yui_css', new CssCompressorFilter(
        //                    '/usr/share/yui-compressor/yui-compressor.jar'
        //            ));
        //            $fm->set('yui_js', new JsCompressorFilter(
        //                    '/usr/share/yui-compressor/yui-compressor.jar'
        //            ));
        //        }),
        //    'assetic.assets' => $this->protect(function($am, $fm) {
        //            $am->set('base_css', new AssetCache(
        //                    new AssetCollection(array(
        //                        new FileAsset(
        //                            __DIR__ . '/../../../vendor/twitter/bootstrap/less/bootstrap.less',
        //                            array(
        //                                new LessFilter(
        //                                    '/usr/local/bin/node', array('/usr/local/lib/node_modules')
        //                                ),
        //                                $fm->get('yui_css'))
        //                        ),
        //                        new FileAsset(__DIR__ . '/../../../views/application.css', array($fm->get('yui_css'))),)
        //                    )
        //                    ,
        //                    new FilesystemCache(__DIR__ . '/../../../cache/assetic')
        //            ));
        //            $am->set('modernizr', new AssetCache(
        //                    new FileAsset(
        //                        __DIR__ . '/../../../resources/assets/modernizr.2.5.3.js'
        //                        , array($fm->get('yui_js')))
        //                    ,
        //                    new FilesystemCache(__DIR__ . '/../../../cache/assetic')
        //            ));
        //            $am->set('jquery', new AssetCache(
        //                    new FileAsset(
        //                        __DIR__ . '/../../../resources/assets/jquery-1.7.2.js'
        //                        , array($fm->get('yui_js')))
        //                    ,
        //                    new FilesystemCache(__DIR__ . '/../../../cache/assetic')
        //            ));
        //            $am->set('bootstrap_js', new AssetCache(
        //                    new AssetCollection(array(
        //                        new FileAsset(__DIR__ . '/../../../vendor/twitter/bootstrap/js/bootstrap-alert.js'),
        //                        new FileAsset(__DIR__ . '/../../../vendor/twitter/bootstrap/js/bootstrap-button.js'),
        //                        new FileAsset(__DIR__ . '/../../../vendor/twitter/bootstrap/js/bootstrap-collapse.js'),
        //                        new FileAsset(__DIR__ . '/../../../vendor/twitter/bootstrap/js/bootstrap-dropdown.js'),
        //                        new FileAsset(__DIR__ . '/../../../vendor/twitter/bootstrap/js/bootstrap-modal.js'),
        //                        new FileAsset(__DIR__ . '/../../../vendor/twitter/bootstrap/js/bootstrap-tooltip.js'),
        //                        new FileAsset(__DIR__ . '/../../../vendor/twitter/bootstrap/js/bootstrap-popover.js'),
        //                        new FileAsset(__DIR__ . '/../../../vendor/twitter/bootstrap/js/bootstrap-scrollspy.js'),
        //                        new FileAsset(__DIR__ . '/../../../vendor/twitter/bootstrap/js/bootstrap-tab.js'),
        //                        new FileAsset(__DIR__ . '/../../../vendor/twitter/bootstrap/js/bootstrap-transition.js'),
        //                        new FileAsset(__DIR__ . '/../../../vendor/twitter/bootstrap/js/bootstrap-typeahead.js'),
        //                        ), array($fm->get('yui_js')))
        //                    ,
        //                    new FilesystemCache(__DIR__ . '/../../../cache/assetic')
        //            ));
        //            $am->set('bootstrap_img', new \Assetic\Asset\AssetCache(
        //                    new FileAsset(__DIR__ . '/../../../vendor/twitter/bootstrap/img/glyphicons-halflings.png'),
        //                    new FilesystemCache(__DIR__ . '/../../../cache/assetic')
        //            ));
        //            $am->get('base_css')->setTargetPath('css/styles.css');
        //            $am->get('bootstrap_img')->setTargetPath('img/glyphicons-halflings.png');
        //            $am->get('modernizr')->setTargetPath('js/modernizr.js');
        //            $am->get('jquery')->setTargetPath('js/jquery.js');
        //            $am->get('bootstrap_js')->setTargetPath('js/bootstrap.js');
        //        })
        //));

        $this['dm'] = $this->share(function (Application $app) {
            return $app['doctrine.odm.mongodb.dm'];
        });

    }
}