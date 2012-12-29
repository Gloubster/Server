<?php

namespace Gloubster\Tests\Server\Component;

use Gloubster\Message\Job\ImageJob;
use Gloubster\Message\Presence\WorkerPresence;
use Gloubster\Server\Component\LogBuilderComponent;
use Gloubster\Tests\GloubsterTest;
use Predis\Client as PredisSync;
use Predis\Async\Client as PredisAsync;
use React\Stomp\Protocol\Frame;
use React\EventLoop\Factory as LoopFactory;

/**
 * @covers Gloubster\Server\Component\LogBuilderComponent
 */
class LogBuilderComponentTest extends GloubsterTest
{
    /** @test */
    public function itShouldRegister()
    {
        $server = $this->getServer();

        $server->register(new LogBuilderComponent());
        $server['dispatcher']->emit('redis-connected', array($server, $this->getPredisAsyncClient(), $this->getPredisAsyncConnection()));
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

        $resolver = $this->getResolver();
        $resolver->expects($this->once())
            ->method('ack');

        $done = false;

        $phpunit = $this;

        $redis = new PredisAsync('tcp://127.0.0.1:6379', $options);
        $redis->connect(function() use ($phpunit, $redis, $frame, $redisSync, &$done, $resolver) {
            $component = new LogBuilderComponent();

            $component->handleLog($redis, $this->getLogger(), $frame, $resolver)
                ->then(function ($hashId) use ($phpunit, $redis, $redisSync, &$done) {

                    $redis->disconnect();

                    $data = $redisSync->hgetall($hashId);

                    $phpunit->assertGreaterThan(0, count($data));
                    $phpunit->assertEquals('Gloubster\Message\Job\ImageJob', $data['type']);

                    $phpunit->assertTrue($redisSync->sismember('jobs', $hashId));

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

        $resolver = $this->getResolver();
        $resolver->expects($this->once())
            ->method('ack');

        $done = false;

        $phpunit = $this;

        $redis = new PredisAsync('tcp://127.0.0.1:6379', $options);
        $redis->connect(function() use ($resolver, $phpunit, $redis, $frame, $redisSync, &$done) {
            $component = new LogBuilderComponent();

            $component->handleLog($redis, $phpunit->getLogger(), $frame, $resolver)
                ->then(function ($hashId) use ($phpunit, $redis, $redisSync, &$done) {

                    $redis->disconnect();

                    $phpunit->assertEquals('{"hello": "world !"}', $redisSync->get($hashId));
                    $phpunit->assertTrue($redisSync->sismember('garbages', $hashId));

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

        $resolver = $this->getResolver();
        $resolver->expects($this->once())
            ->method('ack');

        $done = false;

        $phpunit = $this;

        $redis = new PredisAsync('tcp://127.0.0.1:6379', $options);
        $redis->connect(function() use ($resolver, $phpunit, $redis, $frame, $redisSync, &$done, $worker) {
            $component = new LogBuilderComponent();

            $component->handleLog($redis, $phpunit->getLogger(), $frame, $resolver)
                ->then(function ($hashId) use ($phpunit, $redis, $redisSync, &$done, $worker) {

                    $redis->disconnect();

                    $phpunit->assertEquals($worker->toJson(), $redisSync->get($hashId));
                    $phpunit->assertTrue($redisSync->sismember('garbages', $hashId));

                    $done = true;
                });
        });

        $loop->run();

        $this->assertTrue($done);
    }

    public function testEvents()
    {
        $server = $this->getServer();

        $component = new LogBuilderComponent();
        $component->register($server);

        $server['dispatcher']->emit('redis-connected', array($server, $this->getPredisAsyncClient(), $this->getPredisAsyncConnection()));
        $server['dispatcher']->emit('stomp-connected', array($server, $server['stomp-client']));
        $server['dispatcher']->emit('boot-connected', array($server));
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
