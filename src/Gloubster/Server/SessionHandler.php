<?php
namespace  Gloubster\Server;

use Gloubster\Configuration;
use Gloubster\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcacheSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcachedSessionHandler;

class SessionHandler
{
    public static function factory(Configuration $conf)
    {
        switch (strtolower($conf['session-server']['type'])) {
            case 'memcache':
                $memcache = new \Memcache();
                if (!@$memcache->connect($conf['session-server']['host'], $conf['session-server']['port'])) {
                    throw new RuntimeException(sprintf('Unable to connect to memcache server at %s:%s', $conf['session-server']['host'], $conf['session-server']['port']));
                }

                return new MemcacheSessionHandler($memcache);
                break;
            case 'memcached':
                $memcached = new \Memcached();
                if (!@$memcached->addServer($conf['session-server']['host'], $conf['session-server']['port'])) {
                    throw new RuntimeException(sprintf('Unable to connect to memcached server at %s:%s', $conf['session-server']['host'], $conf['session-server']['port']));
                }

                $memcached->getVersion();

                if (\Memcached::RES_SUCCESS !== $memcached->getResultCode()) {
                    throw new RuntimeException(sprintf('Unable to connect to memcached server at %s:%s', $conf['session-server']['host'], $conf['session-server']['port']));
                }

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
