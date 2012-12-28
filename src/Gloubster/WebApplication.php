<?php

namespace Gloubster;

use Assetic\Asset\AssetCache;
use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Filter\Yui\CssCompressorFilter;
use Assetic\Filter\Yui\JsCompressorFilter;
use Assetic\Cache\FilesystemCache;
use Doctrine\Common\Cache\ArrayCache;
use Gloubster\Configuration;
use Gloubster\Server\SessionHandler;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use Silex\Application as SilexApplication;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\TwigServiceProvider;
use SilexAssetic\AsseticExtension;
use Neutron\Silex\Provider\MongoDBODMServiceProvider;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

class WebApplication extends SilexApplication
{
    public function __construct()
    {
        parent::__construct();
        $this['debug'] = true;

        $this->register(new TwigServiceProvider(), array(
            'twig.path'    => __DIR__ . '/../../views',
            'twig.options' => array(
                'cache'               => __DIR__ . '/../../cache/',
                'strict_variables'    => true,
            )
        ));

        $this->register(new MonologServiceProvider());

        $this['monolog.handler'] = function (WebApplication $app) {
                return new NullHandler();
            };

        $this['monolog.level'] = function () {
                return Logger::DEBUG;
            };

        $this['monolog.name'] = 'Gloubster-Server';

        $this['configuration'] = $this->share(function(WebApplication $app) {
            return new Configuration(file_get_contents(__DIR__ . '/../../config/config.json'), array(
                 file_get_contents(__DIR__ . '/../../resources/configuration.schema.json')
            ));
        });

        $this->register(new SessionServiceProvider(), array(
            'session.storage' => new NativeSessionStorage(array(), SessionHandler::factory($this['configuration'])),
            'session.storage.options' => 'PROUT',
        ));

        $this->register(new MongoDBODMServiceProvider(), array(
            'doctrine.odm.mongodb.connection_options' => array(
                'database'                       => 'gloubster',
                'host'                           => 'localhost',
            ),
            'doctrine.odm.mongodb.documents' => array(
                array(
                    'type'                                       => 'yml',
                    'path'                                       => __DIR__ . '/../../resources/doctrine/documents',
                    'namespace'                                  => 'Gloubster\\Documents'
                ),
            ),
            'doctrine.odm.mongodb.proxies_dir'           => __DIR__ . '/../../cache/doctrine/odm/mongodb/Proxy',
            'doctrine.odm.mongodb.auto_generate_proxies' => true,
            'doctrine.odm.mongodb.hydrators_dir'         => __DIR__ . '/../../cache/doctrine/odm/mongodb/Hydrator',
            'doctrine.odm.mongodb.metadata_cache'        => new ArrayCache(),
        ));

        $this->register(new AsseticExtension(), array(
            'assetic.path_to_web' => __DIR__ . '/../../www/assets',
            'assetic.options'     => array(
                'debug'           => $this['debug'],
            ),
           'assetic.filters' => $this->protect(function($fm) {
                    $fm->set('yui_css', new CssCompressorFilter(
                            '/usr/local/bin/yuicompressor-2.4.7.jar'
                    ));
                    $fm->set('yui_js', new JsCompressorFilter(
                            '/usr/local/bin/yuicompressor-2.4.7.jar'
                    ));
                }),
            'assetic.assets' => $this->protect(function($am, $fm) {
                    $am->set('base_css', new AssetCache(
                            new AssetCollection(array(
                                new FileAsset(
                                    __DIR__ . '/../../components/bootstrap.css/css/bootstrap.css',
                                    array($fm->get('yui_css'))
                                ),
                                new FileAsset(__DIR__ . '/../../views/application.css', array($fm->get('yui_css'))),)
                            )
                            ,
                            new FilesystemCache(__DIR__ . '/../../cache/assetic')
                    ));
                    $am->set('modernizr', new AssetCache(
                            new FileAsset(
                                __DIR__ . '/../../components/modernizr/modernizr.js'
                                , array($fm->get('yui_js')))
                            ,
                            new FilesystemCache(__DIR__ . '/../../cache/assetic')
                    ));
                    $am->set('underscore', new AssetCache(
                                         new FileAsset(
                                             __DIR__ . '/../../components/underscore/underscore.js'
                                             , array($fm->get('yui_js')))
                                         ,
                                         new FilesystemCache(__DIR__ . '/../../cache/assetic')
                                     ));
                    $am->set('backbone', new AssetCache(
                                           new FileAsset(
                                               __DIR__ . '/../../components/backbone/backbone.js'
                                               , array($fm->get('yui_js')))
                                           ,
                                           new FilesystemCache(__DIR__ . '/../../cache/assetic')
                                       ));
                    $am->set('hogan', new AssetCache(
                                        new FileAsset(
                                            __DIR__ . '/../../components/hogan/web/builds/2.0.0/hogan-2.0.0.js'
                                            , array($fm->get('yui_js')))
                                        ,
                                        new FilesystemCache(__DIR__ . '/../../cache/assetic')
                                    ));
                    $am->set('filesizejs', new AssetCache(
                                              new FileAsset(
                                                  __DIR__ . '/../../components/filesize.js/lib/filesize.min.js'
                                                  , array($fm->get('yui_js')))
                                              ,
                                              new FilesystemCache(__DIR__ . '/../../cache/assetic')
                                          ));
                    $am->set('relativedate', new AssetCache(
                                              new FileAsset(
                                                  __DIR__ . '/../../components/relative-date/lib/relative-date.js'
                                                  , array($fm->get('yui_js')))
                                              ,
                                              new FilesystemCache(__DIR__ . '/../../cache/assetic')
                                          ));
                    $am->set('when', new AssetCache(
                                         new FileAsset(
                                             __DIR__ . '/../../components/when/when.js'
                                             , array($fm->get('yui_js')))
                                         ,
                                         new FilesystemCache(__DIR__ . '/../../cache/assetic')
                                     ));
                    $am->set('bootstrap_js', new AssetCache(
                            new AssetCollection(array(
                                new FileAsset(__DIR__ . '/../../components/bootstrap.css/js/bootstrap.js'),
                                ), array($fm->get('yui_js')))
                            ,
                            new FilesystemCache(__DIR__ . '/../../cache/assetic')
                    ));
                    $am->set('bootstrap_img', new \Assetic\Asset\AssetCache(
                            new FileAsset(__DIR__ . '/../../components/bootstrap.css/img/glyphicons-halflings.png'),
                            new FilesystemCache(__DIR__ . '/../../cache/assetic')
                    ));
                    $am->get('base_css')->setTargetPath('css/styles.css');
                    $am->get('bootstrap_img')->setTargetPath('img/glyphicons-halflings.png');
                    $am->get('modernizr')->setTargetPath('js/modernizr.js');
                    $am->get('hogan')->setTargetPath('js/hogan.js');
                    $am->get('filesizejs')->setTargetPath('js/filesize.js');
                    $am->get('relativedate')->setTargetPath('js/relative-date.js');
                    $am->get('backbone')->setTargetPath('js/backbone.js');
                    $am->get('underscore')->setTargetPath('js/underscore.js');
                    $am->get('when')->setTargetPath('js/when.js');
                    $am->get('bootstrap_js')->setTargetPath('js/bootstrap.js');
                })
        ));

        $this['twig'] = $this->share(
            $this->extend('twig', function ($twig, $app) {
                    $twig->setLexer(new \Twig_Lexer($twig, array(
                                                                'tag_comment'  => array('{#', '#}'),
                                                                'tag_block'    => array('{%', '%}'),
                                                                'tag_variable' => array('${', '}'),
                                                           )));

                    return $twig;
                })
        );

        $this['dm'] = $this->share(function (WebApplication $app) {
            return $app['doctrine.odm.mongodb.dm'];
        });
    }
}
