<?php

namespace RedisSentinel;


use RedisSentinel\RedisClient\Adapter\FailedConnectionTest;

class MonitorSetTest extends \PHPUnit_Framework_TestCase
{
    private $monitorSetName = 'name-of-monitor-set';

    /**
     * @return \RedisSentinel\SentinelNode
     */
    private function createMockedSentinelNode()
    {
        //$sentinelNode = \Phake::mock('\RedisSentinel\SentinelNode');
        $sentinelNode = new SentinelNode('127.0.0.1', 2323);

        return $sentinelNode;
    }

    private function mockSentinelWithFailingConnection()
    {
        $sentinelNode = new SentinelNode('127.0.0.1', 2323, new FailedConnectionTest());
        return $sentinelNode;
    }

    public function testAMonitorSetHasAName()
    {
        $monitorSet = new MonitorSet($this->monitorSetName);
        $this->assertEquals($this->monitorSetName, $monitorSet->getName(), 'A monitor set is identified by a name');
    }

    public function testAMonitorSetNameCannotBeEmpty()
    {
        $this->setExpectedException('\\RedisSentinel\\Exception\\InvalidProperty', 'A monitor set needs a valid name');
        new MonitorSet('');
    }

    public function testThatSentinelNodesCanBeAddedToMonitorSets()
    {
        $monitorSet = new MonitorSet($this->monitorSetName);
        $monitorSet->addNode($this->createMockedSentinelNode());
        $this->assertAttributeCount(1, 'nodes', $monitorSet, 'Sentinel node can be added to a monitor set');
    }

    public function testThatOnlySentinelNodeObjectsCanBeAddedAsNode()
    {
        $this->setExpectedException('\\PHPUnit_Framework_Error', 'Argument 1 passed to RedisSentinel\MonitorSet::addNode() must be an instance of RedisSentinel\SentinelNode');
        $monitorSet = new MonitorSet($this->monitorSetName);
        $monitorSet->addNode(new \StdClass());
    }

    public function testThatWeNeedNodesConfigurationToDiscoverAMaster()
    {
        $this->setExpectedException('\\RedisSentinel\\Exception\\ConfigurationError', 'You need to configure and add sentinel nodes before attempting to fetch a master');
        $monitorSet = new MonitorSet($this->monitorSetName);
        $monitorSet->getMaster();
    }

    public function testThatMasterCannotBeFoundIfWeCannotConnectToSentinels()
    {
        $this->setExpectedException('\\RedisSentinel\\Exception\\ConnectionError', 'All sentinels are unreachable');
        $sentinel1 = $this->mockSentinelWithFailingConnection();
        $sentinel2 = $this->mockSentinelWithFailingConnection();
        $monitorSet = new MonitorSet('all-fail');
        $monitorSet->addNode($sentinel1);
        $monitorSet->addNode($sentinel2);
        $monitorSet->getMaster();
    }

}
 