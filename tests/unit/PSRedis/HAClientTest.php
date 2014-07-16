<?php


namespace PSRedis;


use PSRedis\Exception\ConnectionError;

class HAClientTest extends \PHPUnit_Framework_TestCase
{
    public function testThatAnHAClientContainsADependencyOnMasterDiscovery()
    {
        $haclient = new HAClient(new MasterDiscovery('test'));
        $this->assertAttributeInstanceOf('\\PSRedis\\MasterDiscovery', 'masterDiscovery', $haclient, 'The master discovery dependency should be saved in the object');
    }

    public function testThatRedisCommandsAreProxiedToRedisClient()
    {
        // mock master node
        $master = \Phake::mock('\\PSRedis\\Client');
        \Phake::when($master)->get('test')->thenReturn('ok');
        \Phake::when($master)->set('business', 'sparkcentral')->thenReturn(true);

        // mock master discovery
        $masterDiscovery = \Phake::mock('\\PSRedis\\MasterDiscovery');
        \Phake::when($masterDiscovery)->getMaster()->thenReturn($master);

        // testing proxy
        $haclient = new HAClient($masterDiscovery);
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

        // mock master discovery
        $masterDiscovery = \Phake::mock('\\PSRedis\\MasterDiscovery');
        \Phake::when($masterDiscovery)->getMaster()
            ->thenReturn($master)
            ->thenReturn($master);

        // testing proxy
        $haclient = new HAClient($masterDiscovery);
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

        // mock master discovery
        $masterDiscovery = \Phake::mock('\\PSRedis\\MasterDiscovery');
        \Phake::when($masterDiscovery)->getMaster()
            ->thenReturn($master)
            ->thenReturn($master);

        // testing proxy
        $haclient = new HAClient($masterDiscovery);
        $haclient->get('test');
    }

    public function testThatInfiniteLoopsOfRetriesArePrevented()
    {
        $this->setExpectedException('\\PSRedis\\Exception\\ConnectionError');

        // mock master node
        $master = \Phake::mock('\\PSRedis\\Client');
        \Phake::when($master)->get('test')
            ->thenThrow(new ConnectionError());

        // mock master discovery
        $masterDiscovery = \Phake::mock('\\PSRedis\\MasterDiscovery');
        \Phake::when($masterDiscovery)->getMaster()
            ->thenReturn($master);

        // testing proxy
        $haclient = new HAClient($masterDiscovery);
        $haclient->get('test');
    }
}
 