<?php

namespace Gloubster\Client;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider getGoodConfigurations
     */
    public function testGoodConfiguration($configuration)
    {
        $conf = new Configuration($configuration);

        $this->assertTrue(isset($conf['gearman-servers']));
        $this->assertTrue(isset($conf['delivery']));
        $this->assertTrue(isset($conf['delivery']['name']));
        $this->assertTrue(isset($conf['delivery']['configuration']));

        $conf['key'] = 'value';
        $this->assertEquals('value', $conf['key']);
        unset($conf['key']);
        $this->assertFalse(isset($conf['key']));
    }

    /**
     * @dataProvider getWrongConfigurations
     * @expectedException \Gloubster\Exception\RuntimeException
     */
    public function testWrongConfiguration($configuration)
    {
        new Configuration($configuration);
    }

    public function getGoodConfigurations()
    {
        return $this->loadConfigurationsFolder(__DIR__ . '/../../../ressources/good-configurations');
    }

    public function getWrongConfigurations()
    {
        return $this->loadConfigurationsFolder(__DIR__ . '/../../../ressources/wrong-configurations');
    }

    protected function loadConfigurationsFolder($folder)
    {
        $confs = array();

        $finder = new \Symfony\Component\Finder\Finder();

        foreach ($finder->in($folder) as $configuration) {
            $confs[] = array(file_get_contents($configuration->getPathname()));
        }

        return $confs;
    }

}
