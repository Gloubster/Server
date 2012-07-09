<?php

namespace Gloubster\Client;

use Doctrine\MongoDB\Connection;
use Doctrine\ODM\MongoDB\Configuration as DoctrineConfiguration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Monolog\Logger;
use Monolog\Handler\NullHandler;

class JobsContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JobsContainer
     */
    protected $object;
    protected $dir;

    /**
     * @covers Gloubster\Client\JobsContainer::__construct
     */
    protected function setUp()
    {
        $client = $this->getGearmanClientMock();

        $configuration = $this->getConfigurationMock();
        $dm = $this->getDoctrineODMMock();

        $this->object = new JobsContainer($client, $configuration, $dm, $this->getLogger());
    }

    protected function getConfigurationMock()
    {
        $this->dir = tempnam(sys_get_temp_dir(), 'testJobsContainer');
        unlink($this->dir);
        mkdir($this->dir);

        $dir = $this->dir;

        $configuration = $this
            ->getMockBuilder('\\Gloubster\\Client\\Configuration')
            ->disableOriginalConstructor()
            ->getmock();

        $configuration->expects($this->any())
            ->method('offsetGet')
            ->will($this->returnCallback(
                    function ($key) use ($dir) {
                        return array(
                            'name'          => 'FilesystemStore',
                            'configuration' => array('path' => $dir)
                        );
                    }
                ));

        return $configuration;
    }

    protected function getLogger()
    {
        $logger = new Logger('tests');
        $logger->pushHandler(new NullHandler());

        return $logger;
    }

    protected function getGearmanClientMock()
    {
        $client = $this->getMock('\\GearmanClient', array('doBackground', 'jobStatus'));

        $client->expects($this->any())
            ->method('doBackground')
            ->will($this->returnValue('jobHandle-neutron-' . mt_rand()));

        $client->expects($this->any())
            ->method('jobStatus')
            ->will($this->returncallback(function($jobHandle){
                static $n = 0;

                $possible = array(array(false), array(true, false), array(true, true), array(false));

                $ret = $possible[$n];
                if($n > 1) {
                    $n = 3;
                } else {
                    $n++;
                }

                return $ret;
            }));

        return $client;
    }

    protected function getDoctrineODMMock()
    {
        return $this
                ->getMockBuilder('\\Doctrine\\ODM\\MongoDB\\DocumentManager')
                ->disableOriginalConstructor()
                ->getMock();
    }

    /**
     *
     * @return \Doctrine\ODM\MongoDB\DocumentManager
     */
    protected function getDoctrineODM()
    {
        $app = require __DIR__ . '/../../../../src/Gloubster/App.php';

        $dm = $app['dm'];
        $dm->getConnection()->selectDatabase($app['configuration']['client']['mongo-test']['database']);

        $sm = $dm->getSchemaManager();
        $sm->deleteIndexes();
        $sm->dropCollections();
        $sm->dropDatabases();
        $sm->createDatabases();
        $sm->createCollections();
        $sm->ensureIndexes();

        return $dm;
    }

    protected function getDeliveryMock()
    {
        $delivery = $this->getMockBuilder('\\Gloubster\\Delivery\\DeliveryInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $delivery->expects($this->any())
            ->method('retrieve')
            ->will($this->returnCallback(function($uuid){
                return new \Gloubster\Communication\Result('handle', $uuid, serialize('workload'),  file_get_contents(__FILE__), 'worker-romainneutron', microtime(true), microtime(true) + 0.43);
            }));

        return $delivery;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    /**
     * @covers Gloubster\Client\JobsContainer::setCapacity
     * @covers Gloubster\Client\JobsContainer::getCapacity
     */
    public function testGetterSetterCapacity()
    {
        $this->assertInternalType('integer', $this->object->getCapacity());

        $n = mt_rand(1000, 9999);

        $this->object->setCapacity($n);
        $this->assertEquals($n, $this->object->getCapacity());
    }

    /**
     * @covers Gloubster\Client\JobsContainer::setCapacity
     * @expectedException Gloubster\Exception\InvalidArgumentException
     */
    public function testInvalidCapacity()
    {
        $this->object->setCapacity(-1);
    }

    /**
     * @covers Gloubster\Client\JobsContainer::count
     */
    public function testAssertCountable()
    {
        $this->assertEquals(0, count($this->object));
    }

    /**
     * @covers Gloubster\Client\JobsContainer::setDelivery
     * @covers Gloubster\Client\JobsContainer::getDelivery
     */
    public function testDelivery()
    {
        $this->assertInstanceOf('\\Gloubster\\Delivery\\DeliveryInterface', $this->object->getDelivery());
        $delivery = $this->getDeliveryMock();
        $this->object->setDelivery($delivery);
        $this->assertEquals($delivery, $this->object->getDelivery());
    }

    /**
     * @covers Gloubster\Client\JobsContainer::drain
     * @covers Gloubster\Client\JobsContainer::removeJob
     * @covers Gloubster\Client\JobsContainer::updateSpecification
     */
    public function testDrain()
    {
        $client = $this->getGearmanClientMock();
        $capacity = 5;
        $quantity = 50;
        $configuration = $this->getConfigurationMock();
        $dm = $this->getDoctrineODM();

        $this->fillDB($dm, $quantity);

        $object = new JobsContainer($client, $configuration, $dm, $this->getLogger());
        $object->setCapacity($capacity);
        $object->fill();
        $object->setDelivery($this->getDeliveryMock());

        $removed = $capacity - 2;
        $removedUuid = $object->drain();

        $repo = $dm->getRepository('Gloubster\Documents\Specification');
        foreach($removedUuid as $uuid) {
            $spec = $repo->find($uuid);
            $this->assertNotNull($spec->getJobHandle());
            $this->assertTrue($spec->getDone());
            $this->assertFalse($spec->getError());
            $this->assertInternalType('float', $spec->getStart());
            $this->assertInternalType('float', $spec->getStop());
        }

        $this->assertEquals($removed, count($removedUuid));

        $this->assertEquals($capacity-$removed, count($object));

        $remaining = count($object);
        $this->assertEquals($remaining, count($object->drain()));
        $this->assertEquals(0, count($object));
        $object->fill();
        $this->assertEquals($capacity, count($object));

    }

    protected function fillDB($dm, $quantity = 50)
    {
        if($quantity % 10 !== 0 || $quantity < 10) {
            throw new \Exception('Quantity must be a multiple of 10');
        }

        $files = array(
            'http://www.imagelibrary.net/image1.jpg',
            'http://www.imagelibrary.net/image2.jpg',
            'http://www.imagelibrary.net/image3.jpg',
            'http://www.imagelibrary.net/image4.jpg',
            'http://www.imagelibrary.net/image5.jpg',
            'http://www.imagelibrary.net/image6.jpg',
            'http://www.imagelibrary.net/image7.jpg',
            'http://www.imagelibrary.net/image8.jpg',
            'http://www.imagelibrary.net/image9.jpg',
            'http://www.imagelibrary.net/image10.jpg',
        );

        foreach ($files as $file) {
            $jobset = new \Gloubster\Documents\JobSet();
            $jobset->setFile($file);

            for ($i = 0; $i < ($quantity / 10); $i ++ ) {
                $specification = new \Gloubster\Documents\Specification();
                $specification->setName('image');

                $parameter = new \Gloubster\Documents\Parameter();
                $parameter->setName('width');
                $parameter->setValue(mt_rand(100, 300));

                $dm->persist($parameter);

                $specification->addParameters($parameter);

                $parameter = new \Gloubster\Documents\Parameter();
                $parameter->setName('height');
                $parameter->setValue(mt_rand(100, 300));

                $dm->persist($parameter);

                $specification->addParameters($parameter);
                $specification->setJobset($jobset);

                $dm->persist($specification);
            }

            $jobset->addSpecifications($specification);

            $dm->persist($jobset);
        }

        $dm->flush();
    }

    /**
     * @covers Gloubster\Client\JobsContainer::fill
     * @covers Gloubster\Client\JobsContainer::parametersToArray
     * @covers Gloubster\Client\JobsContainer::getJobName
     * @covers Gloubster\Client\JobsContainer::addJob
     */
    public function testFill()
    {
        $client = $this->getGearmanClientMock();

        $capacity = 5;
        $quantity = 50;

        $configuration = $this->getConfigurationMock();
        $dm = $this->getDoctrineODM();

        $this->fillDB($dm, $quantity);

        $object = new JobsContainer($client, $configuration, $dm, $this->getLogger());
        $object->setCapacity($capacity);

        $this->assertEquals($quantity, count($dm->getRepository('Gloubster\Documents\Specification')->findAll()));

        $object->fill();

        $this->assertEquals($capacity, count($object));
        $object->fill();

        $this->assertEquals($capacity, count($object));

        $this->assertEquals($quantity - $capacity, count($dm->getRepository('Gloubster\Documents\Specification')->findBy(array('jobHandle' => null))));
    }

    public  function testFunctionnal()
    {
        $loops = 0;


        $client = $this->getGearmanClientMock();
        $capacity = 5;
        $quantity = 50;
        $configuration = $this->getConfigurationMock();
        $dm = $this->getDoctrineODM();

        $this->fillDB($dm, $quantity);

        $object = new JobsContainer($client, $configuration, $dm, $this->getLogger());
        $object->setCapacity($capacity);
        $object->fill();
        $object->setDelivery($this->getDeliveryMock());

        $removedUuids = array();

        $repo = $dm->getRepository('Gloubster\Documents\Specification');

        while(count($object) > 0)
        {
            $removedUuid = $object->drain();

            foreach($removedUuid as $uuid){

                $this->assertNotContains($uuid, $removedUuids);
                $removedUuids[] = $uuid;

                $spec = $repo->find($uuid);
                $this->assertNotNull($spec->getJobHandle());
                $this->assertTrue($spec->getDone());
                $this->assertFalse($spec->getError());
                $this->assertInternalType('float', $spec->getStart());
                $this->assertInternalType('float', $spec->getStop());
            }

            $object->fill();
            $object->fill();
            $loops++;
        }

        $expected = ceil(($quantity + 2) / $capacity);

        $this->assertEquals($expected, $loops);
    }

    /**
     * @covers Gloubster\Client\JobsContainer::ping
     * @todo Implement testPing().
     */
    public function testPing()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}

?>
