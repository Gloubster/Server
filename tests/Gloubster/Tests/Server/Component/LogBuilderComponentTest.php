<?php

namespace Gloubster\Tests\Server\Component;

use Gloubster\Configuration;
use Gloubster\Message\Job\ImageJob;
use Gloubster\Message\Presence\WorkerPresence;
use Gloubster\Server\GloubsterServer;
use Gloubster\Server\Component\LogBuilderComponent;
use Predis\Client as PredisSync;
use Predis\Async\Client as PredisAsync;
use React\Stomp\Protocol\Frame;
use React\EventLoop\Factory as LoopFactory;

class LogBuilderComponentTest extends \PHPUnit_Framework_TestCase
{

    /** @test */
    public function itShouldRegister()
    {
        $websocket = $this->getMockBuilder('Gloubster\Server\WebsocketApplication')
            ->disableOriginalConstructor()
            ->getMock();

        $client = $this->getMockBuilder('React\Stomp\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $conf = new Configuration('{
            "server": {
                "host": "localhost",
                "port": 5672,
                "user": "guest",
                "password": "guest",
                "vhost": "/",
                "server-management": {
                    "port": 55672,
                    "scheme": "http"
                },
                "stomp-gateway": {
                    "port": 61613
                }
            },
            "redis-server": {
                "host": "localhost",
                "port": 6379
            },
            "session-server": {
                "type": "memcache",
                "host": "localhost",
                "port": 11211
            },
            "websocket-server": {
                "address": "local.gloubster",
                "port": 9990
            },
            "listeners": [
                {
                    "type": "' . str_replace('\\', '\\\\', __NAMESPACE__) . '\\\\ListenerTester",
                    "options": {
                        "transport": "tcp",
                        "address": "0.0.0.0",
                        "port": 22345
                    }
                }
            ]
        }');

        $loop->created = null;

        $server = new GloubsterServer($websocket, $client, $loop, $conf, $this->getLogger());
        $server->register(new LogBuilderComponent());

        // this attach listeners to the stomp server
        $server->activateRedisServices(
            $this->getMockBuilder('Predis\\Async\\Client')
                ->disableOriginalConstructor()
                ->getMock(), $this->getMockBuilder('Predis\Async\Connection\ConnectionInterface')
                ->disableOriginalConstructor()
                ->getMock()
        );
    }

    public function testHandleLogWithJob()
    {
        $job = new ImageJob();
        $job->setBeginning('begin');
        $job->setEnd('end');

        $frame = new Frame('MESSAGE', array('delivery_tag' => 'delivery-' . mt_rand()), $job->toJson());

        $loop = LoopFactory::create();
        $options = array(
            'eventloop' => $loop,
            'on_error'  => array($this, 'throwRedisError'),
        );

        $redisSync = new PredisSync('tcp://127.0.0.1:6379');
        $redisSync->connect();

        $done = false;

        $redis = new PredisAsync('tcp://127.0.0.1:6379', $options);
        $redis->connect(function() use ($redis, $frame, $redisSync, &$done) {
            $component = new LogBuilderComponent();

            $resolver = $this->getResolver();
            $resolver->expects($this->once())
                ->method('ack');

            $component->handleLog($redis, $this->getLogger(), $frame, $resolver)
                ->then(function ($hashId) use ($redis, $redisSync, &$done) {

                    $redis->disconnect();

                    $data = $redisSync->hgetall($hashId);

                    $this->assertGreaterThan(0, count($data));
                    $this->assertEquals('Gloubster\Message\Job\ImageJob', $data['type']);

                    $this->assertTrue($redisSync->sismember('jobs', $hashId));

                    $done = true;
                });
        });

        $loop->run();

        $this->assertTrue($done);
    }

    public function testHandleLogWithWrongJob()
    {
        $frame = new Frame('MESSAGE', array('delivery_tag' => 'delivery-' . mt_rand()), '{"hello": "world !"}');

        $loop = LoopFactory::create();
        $options = array(
            'eventloop' => $loop,
            'on_error'  => array($this, 'throwRedisError'),
        );

        $redisSync = new PredisSync('tcp://127.0.0.1:6379');
        $redisSync->connect();

        $done = false;

        $redis = new PredisAsync('tcp://127.0.0.1:6379', $options);
        $redis->connect(function() use ($redis, $frame, $redisSync, &$done) {
            $component = new LogBuilderComponent();

            $resolver = $this->getResolver();
            $resolver->expects($this->once())
                ->method('ack');

            $component->handleLog($redis, $this->getLogger(), $frame, $resolver)
                ->then(function ($hashId) use ($redis, $redisSync, &$done) {

                    $redis->disconnect();

                    $this->assertEquals('{"hello": "world !"}', $redisSync->get($hashId));
                    $this->assertTrue($redisSync->sismember('garbages', $hashId));

                    $done = true;
                });
        });

        $loop->run();

        $this->assertTrue($done);
    }

    public function testHandleLogWithGoodMessageNotImplementingJobInterface()
    {
        $worker = new WorkerPresence();
        $worker->setMemory(12345);

        $frame = new Frame('MESSAGE', array('delivery_tag' => 'delivery-' . mt_rand()), $worker->toJson());

        $loop = LoopFactory::create();
        $options = array(
            'eventloop' => $loop,
            'on_error'  => array($this, 'throwRedisError'),
        );

        $redisSync = new PredisSync('tcp://127.0.0.1:6379');
        $redisSync->connect();

        $done = false;

        $redis = new PredisAsync('tcp://127.0.0.1:6379', $options);
        $redis->connect(function() use ($redis, $frame, $redisSync, &$done, $worker) {
            $component = new LogBuilderComponent();

            $resolver = $this->getResolver();
            $resolver->expects($this->once())
                ->method('ack');

            $component->handleLog($redis, $this->getLogger(), $frame, $resolver)
                ->then(function ($hashId) use ($redis, $redisSync, &$done, $worker) {

                    $redis->disconnect();

                    $this->assertEquals($worker->toJson(), $redisSync->get($hashId));
                    $this->assertTrue($redisSync->sismember('garbages', $hashId));

                    $done = true;
                });
        });

        $loop->run();

        $this->assertTrue($done);
    }

    public function testRegisterSTOMPServicesProducesNoError()
    {
        $server = $this->getMockBuilder('Gloubster\\Server\\GloubsterServer')
            ->disableOriginalConstructor()
            ->getMock();

        $stomp = $this->getMockBuilder('React\\Stomp\\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $component = new LogBuilderComponent();
        $component->registerSTOMP($server, $stomp);
    }

    public function testThatRegisterSTOMPDoesNotThrowError()
    {
        $server = $this->getMockBuilder('Gloubster\\Server\\GloubsterServer')
                    ->disableOriginalConstructor()
                    ->getMock();

        $stomp = $this->getMockBuilder('React\\Stomp\\Client')
                    ->disableOriginalConstructor()
                    ->getMock();

        $component = new LogBuilderComponent();
        $component->registerSTOMP($server, $stomp);
    }

    public function testThatBootDoesNotThrowError()
    {
        $server = $this->getMockBuilder('Gloubster\\Server\\GloubsterServer')
                    ->disableOriginalConstructor()
                    ->getMock();

        $client = $this->getMockBuilder('Predis\\Async\\Client')
                    ->disableOriginalConstructor()
                    ->getMock();

        $conn = $this->getMockBuilder('Predis\Async\Connection\ConnectionInterface')
                    ->disableOriginalConstructor()
                    ->getMock();

        $component = new LogBuilderComponent();
        $component->boot($server);
    }

    private function getLogger()
    {
        return $this->getMockBuilder('Monolog\\Logger')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getResolver()
    {
        return $this->getMockBuilder('React\\Stomp\\AckResolver')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function throwRedisError($client, $exception, $conn)
    {
        throw $exception;
    }
}
