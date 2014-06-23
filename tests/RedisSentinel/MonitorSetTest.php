<?php

namespace RedisSentinel;


class MonitorSetTest extends \PHPUnit_Framework_TestCase
{
    private $monitorSetName = 'name-of-monitor-set';

    /**
     * @return \RedisSentinel\SentinelNode
     */
    private function createMockedSentinelNode()
    {
        $sentinelNode = \Phake::mock('RedisSentinel\SentinelNode');

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
        $monitorSet = new MonitorSet('');
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
}
 