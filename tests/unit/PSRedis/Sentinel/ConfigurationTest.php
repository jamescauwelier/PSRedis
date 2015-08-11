<?php
namespace PSRedis\Sentinel;

use PSRedis\Client;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testThatWeCanConfigureSentinels()
    {
        $configuration = new Configuration();
        $configuration->addSentinel(new Client('127.0.0.1', 2323));
        $this->assertCount(1, $configuration->getSentinels());
        $configuration->addSentinel(new Client('127.0.0.1', 2323));
        $this->assertCount(2, $configuration->getSentinels());
    }

    public function testThatSentinelClientsNeedToBeConfigured()
    {
        $this->setExpectedException('\\PHPUnit_Framework_Error', 'Argument 1 passed to PSRedis\Sentinel\Configuration::addSentinel() must be an instance of PSRedis\Client');
        $configuration = new Configuration();
        $configuration->addSentinel(new \StdClass());
    }
}