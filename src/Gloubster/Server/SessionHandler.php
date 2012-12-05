<?php
namespace  Gloubster\Server;

use Gloubster\Configuration;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcacheSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcachedSessionHandler;
use Gloubster\Exception\RuntimeException;

class SessionHandler
{
    public static function factory(Configuration $conf)
    {
        switch (strtolower($conf['session-server']['type'])) {
            case 'memcache':
                $memcache = new \Memcache();
                $memcache->connect($conf['session-server']['host'], $conf['session-server']['port']);

                return new MemcacheSessionHandler($memcache);
                break;
            case 'memcached':
                $memcached = new \Memcached();
                $memcached->addServer($conf['session-server']['host'], $conf['session-server']['port']);

                return new MemcachedSessionHandler($memcached);
                break;
            case 'mongo':
            case 'pdo':
                throw new RuntimeException(sprintf('Session handler %s is not yet supported', $conf['session-server']['type']));
                break;
            default:
                throw new RuntimeException(sprintf('Session handler %s is not a valid type', $conf['session-server']['type']));
                break;
        }
    }
}
