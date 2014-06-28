<?php

namespace RedisSentinel;


use RedisSentinel\Exception\ConnectionError;

class MonitorSetTest extends \PHPUnit_Framework_TestCase
{
    private $monitorSetName = 'name-of-monitor-set';
    private $ipAddress = '127.0.0.1';
    private $port = 2323;

    /**
     * @return \RedisSentinel\SentinelNode
     */
    private function mockOnlineSentinel()
    {
        $sentinelNode = \Phake::mock('\\RedisSentinel\\SentinelNode');
        \Phake::when($sentinelNode)->connect()->thenReturn(null);
        \Phake::when($sentinelNode)->getIpAddress()->thenReturn($this->ipAddress);
        \Phake::when($sentinelNode)->getPort()->thenReturn($this->port);

        return $sentinelNode;
    }

    /**
     * @return \RedisSentinel\SentinelNode
     */
    private function mockSentinelWithFailingConnection()
    {
        $sentinelNode = \Phake::mock('\\RedisSentinel\\SentinelNode');
        \Phake::when($sentinelNode)->connect()->thenThrow(
            new ConnectionError(sprintf('Could not connect to sentinel at %s:%d', $this->ipAddress, $this->port))
        );
        \Phake::when($sentinelNode)->getIpAddress()->thenReturn($this->ipAddress);
        \Phake::when($sentinelNode)->getPort()->thenReturn($this->port);

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
        $monitorSet->addNode($this->mockOnlineSentinel());
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
 