<?php


namespace PSRedis;


use PSRedis\Exception\ConnectionError;
use PSRedis\NodeDiscovery\MasterDiscovery;
use PSRedis\Sentinel\Configuration;

class HAClientTest extends \PHPUnit_Framework_TestCase
{
    public function testThatAnHAClientContainsADependencyOnMasterDiscovery()
    {
        $haclient = new HAClient(new Configuration('test'), new MasterDiscovery('test'));
        $this->assertAttributeInstanceOf('\\PSRedis\\Sentinel\\Configuration', 'sentinelConfiguration', $haclient);
        $this->assertAttributeInstanceOf('\\PSRedis\\NodeDiscovery\\MasterDiscovery', 'masterDiscovery', $haclient, 'The master discovery dependency should be saved in the object');
    }

    public function testThatRedisCommandsAreProxiedToRedisClient()
    {
        // mock master node
        $master = \Phake::mock('\\PSRedis\\Client');
        \Phake::when($master)->get('test')->thenReturn('ok');
        \Phake::when($master)->set('business', 'sparkcentral')->thenReturn(true);

        // configure sentinel nodes
        $sentinelConfiguration = new Configuration('test');

        // mock master discovery
        $masterDiscovery = \Phake::mock('\\PSRedis\\NodeDiscovery\\MasterDiscovery');
        \Phake::when($masterDiscovery)->getNode($sentinelConfiguration)->thenReturn($master);

        // testing proxy
        $haclient = new HAClient($sentinelConfiguration, $masterDiscovery);
        $this->assertEquals('ok', $haclient->get('test'), 'Redis command "GET" is proxied to the master node');
        $this->assertEquals(true, $haclient->set('business', 'sparkcentral'), 'Redis command "SET" is proxied to the master node');
    }

    public function testThatAFailingRedisCommandsIsRetried()
    {
        // mock master node
        $master = \Phake::mock('\\PSRedis\\Client');
        \Phake::when($master)->get('test')
            ->thenThrow(new ConnectionError())
            ->thenReturn('ok');

        // sentinel nodes configuration
        $sentinelConfiguration = new Configuration('test');

        // mock master discovery
        $masterDiscovery = \Phake::mock('\\PSRedis\\NodeDiscovery\\MasterDiscovery');
        \Phake::when($masterDiscovery)->getNode($sentinelConfiguration)
            ->thenReturn($master)
            ->thenReturn($master);

        // testing proxy
        $haclient = new HAClient($sentinelConfiguration, $masterDiscovery);
        $this->assertEquals('ok', $haclient->get('test'), 'HAClient automatically retries on connection errors');
    }

    public function testThatOnlyConnectionErrorsAreTriggeringFailover()
    {
        $this->setExpectedException('\\Predis\\CommunicationException');

        // mock master node
        $master = \Phake::mock('\\PSRedis\\Client');
        \Phake::when($master)->get('test')
            ->thenThrow(\Phake::mock('\\Predis\\CommunicationException'))
            ->thenReturn('ok');

        // sentinel nodes configuration
        $sentinelConfiguration = new Configuration('test');

        // mock master discovery
        $masterDiscovery = \Phake::mock('\\PSRedis\\NodeDiscovery\\MasterDiscovery');
        \Phake::when($masterDiscovery)->getNode($sentinelConfiguration)
            ->thenReturn($master)
            ->thenReturn($master);

        // testing proxy
        $haclient = new HAClient($sentinelConfiguration, $masterDiscovery);
        $haclient->get('test');
    }

    public function testThatInfiniteLoopsOfRetriesArePrevented()
    {
        $this->setExpectedException('\\PSRedis\\Exception\\ConnectionError');

        // mock master node
        $master = \Phake::mock('\\PSRedis\\Client');
        \Phake::when($master)->get('test')
            ->thenThrow(new ConnectionError());

        // sentinel nodes configuration
        $sentinelConfiguration = new Configuration('test');

        // mock master discovery
        $masterDiscovery = \Phake::mock('\\PSRedis\\NodeDiscovery\\MasterDiscovery');
        \Phake::when($masterDiscovery)->getNode($sentinelConfiguration)
            ->thenReturn($master);

        // testing proxy
        $haclient = new HAClient($sentinelConfiguration, $masterDiscovery);
        $haclient->get('test');
    }
}
 