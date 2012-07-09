<?php

namespace Gloubster\Client;

require_once dirname(__FILE__) . '/../../../../src/Gloubster/Client/Client.php';

class ClientTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @covers Gloubster\Client\Client::__construct
     */
    public function testLoad()
    {
        $dir = tempnam(sys_get_temp_dir(), 'clienttest');
        unlink($dir);
        mkdir($dir);

        $host = 'kangaroo';
        $port = 12345;
        $that = $this;

        $conf = array(
            'gearman-servers' => array(
                array(
                    'host'     => $host,
                    'port'     => $port
                )
            ),
            'delivery' => array(
                'name'          => 'FilesystemStore',
                'configuration' => array(
                    'path'   => $dir
                )
            ),
            'client' => array(
                'stack'  => 20,
                'period' => 1000,
                'mongo'=>array(
                    'host'=>'localhost'
                )
            )
        );

        $logger = new \Monolog\Logger('test');

        $gearmanClient = $this->getGearmanClientMock();
        $gearmanClient->expects($this->once())
            ->method('addServer')
            ->will($this->returnCallback(function($calledHost, $calledPort) use ($that, $host, $port) {
                        $that->assertEquals($host, $calledHost);
                        $that->assertEquals($port, $calledPort);
                    }));

        new Client($gearmanClient, new Configuration(json_encode($conf)), $this->getDoctrineODMMock(), $logger);
    }

    protected function getGearmanClientMock()
    {
        return $this->getMock('\\GearmanClient', array());
    }

    protected function getDoctrineODMMock()
    {
        return $this->getMock('\\Doctrine\\ODM\\MongoDB\\DocumentManager', array(), array(), '', false);
    }

    /**
     * @covers Gloubster\Client\Client::run
     * @todo Implement testRun().
     */
    public function testRun()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

}
