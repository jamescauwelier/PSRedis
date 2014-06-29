<?php

namespace Redis;

require_once __DIR__.'/Client/Adapter/Predis/MockedPredisClientCreator.php';

use Redis\Client\Adapter\Predis\MockedPredisClientCreator;
use Redis\Exception\ConnectionError;
use Redis\Client\Adapter\PredisSentinelClientAdapter;

class MonitorSetTest extends \PHPUnit_Framework_TestCase
{
    private $monitorSetName = 'name-of-monitor-set';

    private $onlineSentinelIpAddress = '127.0.0.1';
    private $onlineSentinelPort = 2424;

    private $onlineMasterIpAddress = '198.100.10.1';
    private $onlineMasterPort = 5050;

    private $offlineSentinelIpAddress = '127.0.0.1';
    private $offlineSentinelPort = 2323;

    /**
     * @return \Redis\Client
     */
    private function mockOnlineSentinel()
    {
        $clientAdapter = new PredisSentinelClientAdapter(new MockedPredisClientCreator());

        $redisClient = \Phake::mock('\\Redis\Client');
        \Phake::when($redisClient)->getIpAddress()->thenReturn($this->onlineMasterIpAddress);
        \Phake::when($redisClient)->getPort()->thenReturn($this->onlineMasterPort);

        $sentinelClient = \Phake::mock('\\Redis\\Client');
        \Phake::when($redisClient)->isMaster()->thenReturn(true);
        \Phake::when($sentinelClient)->connect()->thenReturn(null);
        \Phake::when($sentinelClient)->getIpAddress()->thenReturn($this->onlineSentinelIpAddress);
        \Phake::when($sentinelClient)->getPort()->thenReturn($this->onlineSentinelPort);
        \Phake::when($sentinelClient)->getClientAdapter()->thenReturn($clientAdapter);
        \Phake::when($sentinelClient)->getMaster()->thenReturn($redisClient);

        return $sentinelClient;
    }

    /**
     * @return \Redis\Client
     */
    private function mockOfflineSentinel()
    {
        $sentinelClient = \Phake::mock('\\Redis\\Client');
        \Phake::when($sentinelClient)->connect()->thenThrow(
            new ConnectionError(sprintf('Could not connect to sentinel at %s:%d', $this->offlineSentinelIpAddress, $this->offlineSentinelPort))
        );
        \Phake::when($sentinelClient)->getIpAddress()->thenReturn($this->offlineSentinelIpAddress);
        \Phake::when($sentinelClient)->getPort()->thenReturn($this->offlineSentinelPort);

        return $sentinelClient;
    }

    public function testAMonitorSetHasAName()
    {
        $monitorSet = new MonitorSet($this->monitorSetName);
        $this->assertEquals($this->monitorSetName, $monitorSet->getName(), 'A monitor set is identified by a name');
    }

    public function testAMonitorSetNameCannotBeEmpty()
    {
        $this->setExpectedException('\\Redis\\Exception\\InvalidProperty', 'A monitor set needs a valid name');
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
        $this->setExpectedException('\\PHPUnit_Framework_Error', 'Argument 1 passed to Redis\MonitorSet::addNode() must be an instance of Redis\Client');
        $monitorSet = new MonitorSet($this->monitorSetName);
        $monitorSet->addNode(new \StdClass());
    }

    public function testThatWeNeedNodesConfigurationToDiscoverAMaster()
    {
        $this->setExpectedException('\\Redis\\Exception\\ConfigurationError', 'You need to configure and add sentinel nodes before attempting to fetch a master');
        $monitorSet = new MonitorSet($this->monitorSetName);
        $monitorSet->getMaster();
    }

    public function testThatMasterCannotBeFoundIfWeCannotConnectToSentinels()
    {
        $this->setExpectedException('\\Redis\\Exception\\ConnectionError', 'All sentinels are unreachable');
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

        $this->assertInstanceOf('\\Redis\\Client', $masterNode, 'The master returned should be an instance of \\Redis\\Client');
        $this->assertEquals($this->onlineMasterIpAddress, $masterNode->getIpAddress(), 'The master node IP address returned should be the one of the online sentinel');
        $this->assertEquals($this->onlineMasterPort, $masterNode->getPort(), 'The master node IP port returned should be the one of the online sentinel');
    }

}
 