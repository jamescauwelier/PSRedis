<?php

namespace Sentinel;


use Sentinel\Exception\ConnectionError;
use Sentinel\Client\Adapter\PredisSentinelClientAdapter;

class MonitorSetTest extends \PHPUnit_Framework_TestCase
{
    private $monitorSetName = 'name-of-monitor-set';

    private $onlineIpAddress = '127.0.0.1';
    private $onlinePort = 2424;

    private $offlineIpAddress = '127.0.0.1';
    private $offlinePort = 2323;

    /**
     * @return \Sentinel\Client
     */
    private function mockOnlineSentinel()
    {
        $clientAdapter = new PredisSentinelClientAdapter();
        $sentinelClient = \Phake::mock('\\Sentinel\\Client');
        \Phake::when($sentinelClient)->connect()->thenReturn(null);
        \Phake::when($sentinelClient)->getIpAddress()->thenReturn($this->onlineIpAddress);
        \Phake::when($sentinelClient)->getPort()->thenReturn($this->onlinePort);
        \Phake::when($sentinelClient)->getClientAdapter()->thenReturn($clientAdapter);

        return $sentinelClient;
    }

    /**
     * @return \Sentinel\Client
     */
    private function mockOfflineSentinel()
    {
        $sentinelClient = \Phake::mock('\\Sentinel\\Client');
        \Phake::when($sentinelClient)->connect()->thenThrow(
            new ConnectionError(sprintf('Could not connect to sentinel at %s:%d', $this->offlineIpAddress, $this->offlinePort))
        );
        \Phake::when($sentinelClient)->getIpAddress()->thenReturn($this->offlineIpAddress);
        \Phake::when($sentinelClient)->getPort()->thenReturn($this->offlinePort);

        return $sentinelClient;
    }

    public function testAMonitorSetHasAName()
    {
        $monitorSet = new MonitorSet($this->monitorSetName);
        $this->assertEquals($this->monitorSetName, $monitorSet->getName(), 'A monitor set is identified by a name');
    }

    public function testAMonitorSetNameCannotBeEmpty()
    {
        $this->setExpectedException('\\Sentinel\\Exception\\InvalidProperty', 'A monitor set needs a valid name');
        new MonitorSet('');
    }

    public function testThatSentinelClientsCanBeAddedToMonitorSets()
    {
        $monitorSet = new MonitorSet($this->monitorSetName);
        $monitorSet->addNode($this->mockOnlineSentinel());
        $this->assertAttributeCount(1, 'nodes', $monitorSet, 'Sentinel node can be added to a monitor set');
    }

    public function testThatOnlySentinelClientObjectsCanBeAddedAsNode()
    {
        $this->setExpectedException('\\PHPUnit_Framework_Error', 'Argument 1 passed to Sentinel\MonitorSet::addNode() must be an instance of Sentinel\Client');
        $monitorSet = new MonitorSet($this->monitorSetName);
        $monitorSet->addNode(new \StdClass());
    }

    public function testThatWeNeedNodesConfigurationToDiscoverAMaster()
    {
        $this->setExpectedException('\\Sentinel\\Exception\\ConfigurationError', 'You need to configure and add sentinel nodes before attempting to fetch a master');
        $monitorSet = new MonitorSet($this->monitorSetName);
        $monitorSet->getMaster();
    }

    public function testThatMasterCannotBeFoundIfWeCannotConnectToSentinels()
    {
        $this->setExpectedException('\\Sentinel\\Exception\\ConnectionError', 'All sentinels are unreachable');
        $sentinel1 = $this->mockOfflineSentinel();
        $sentinel2 = $this->mockOfflineSentinel();
        $monitorSet = new MonitorSet('all-fail');
        $monitorSet->addNode($sentinel1);
        $monitorSet->addNode($sentinel2);
        $monitorSet->getMaster();
    }

    public function testThatSentinelNodeIsReturnedOnSuccessfulMasterDiscovery()
    {
        $sentinel1 = $this->mockOfflineSentinel();
        $sentinel2 = $this->mockOnlineSentinel();
        $monitorSet = new MonitorSet('online-sentinel');
        $monitorSet->addNode($sentinel1);
        $monitorSet->addNode($sentinel2);
        $masterNode = $monitorSet->getMaster();

        $this->assertInstanceOf('\\Sentinel\\Client\\SentinelClientAdapter', $masterNode, 'The master returned should be an instance of a Client adapter');
        $this->assertEquals($sentinel2->getClientAdapter(), $masterNode, 'The master node returned should be the one of the online sentinel');
    }

}
 