<?php

namespace PSRedis;

use PSRedis\Client\Adapter\Predis\Mock\MockedPredisClientCreatorWithNoMasterAddress;
use PSRedis\MasterDiscovery\BackoffStrategy\Incremental;
use PSRedis\Exception\ConnectionError;
use PSRedis\Client\Adapter\PredisClientAdapter;
use PSRedis\MasterDiscovery\BackoffStrategy\None;

class MasterDiscoveryTest extends \PHPUnit_Framework_TestCase
{
    private $masterName = 'name-of-master';

    private $onlineSentinelIpAddress = '127.0.0.1';
    private $onlineSentinelPort = 2424;

    private $onlineMasterIpAddress = '198.100.10.1';
    private $onlineMasterPort = 5050;

    private $onlineSteppingDownMasterIpAddress = '198.100.10.1';
    private $onlineSteppingDownMasterPort = 5050;

    private $offlineSentinelIpAddress = '127.0.0.1';
    private $offlineSentinelPort = 2323;

    private $observedBackoff = false;

    /**
     * @return \PRedis\Client
     */
    private function mockOnlineSentinel()
    {
        $clientAdapter = new PredisClientAdapter(new MockedPredisClientCreatorWithNoMasterAddress(), Client::TYPE_SENTINEL);

        $redisClient = \Phake::mock('\\PSRedis\Client');
        \Phake::when($redisClient)->getIpAddress()->thenReturn($this->onlineMasterIpAddress);
        \Phake::when($redisClient)->getPort()->thenReturn($this->onlineMasterPort);
        \Phake::when($redisClient)->isMaster()->thenReturn(true);
        \Phake::when($redisClient)->getRole()->thenReturn(Client::ROLE_MASTER);

        $sentinelClient = \Phake::mock('\\PSRedis\\Client');
        \Phake::when($sentinelClient)->connect()->thenReturn(null);
        \Phake::when($sentinelClient)->getIpAddress()->thenReturn($this->onlineSentinelIpAddress);
        \Phake::when($sentinelClient)->getPort()->thenReturn($this->onlineSentinelPort);
        \Phake::when($sentinelClient)->getClientAdapter()->thenReturn($clientAdapter);
        \Phake::when($sentinelClient)->getMaster(\Phake::anyParameters())->thenReturn($redisClient);

        return $sentinelClient;
    }

    /**
     * @return \PSRedis\Client
     */
    private function mockOfflineSentinel()
    {
        $sentinelClient = \Phake::mock('\\PSRedis\\Client');
        \Phake::when($sentinelClient)->connect()->thenThrow(
            new ConnectionError(sprintf('Could not connect to sentinel at %s:%d', $this->offlineSentinelIpAddress, $this->offlineSentinelPort))
        );
        \Phake::when($sentinelClient)->getIpAddress()->thenReturn($this->offlineSentinelIpAddress);
        \Phake::when($sentinelClient)->getPort()->thenReturn($this->offlineSentinelPort);

        return $sentinelClient;
    }

    /**
     * @return \PSRedis\Client
     */
    private function mockTemporaryOfflineSentinel()
    {
        // mock a master node
        $masterNode = \Phake::mock('\\PSRedis\Client');
        \Phake::when($masterNode)->getIpAddress()->thenReturn($this->onlineMasterIpAddress);
        \Phake::when($masterNode)->getPort()->thenReturn($this->onlineMasterPort);
        \Phake::when($masterNode)->isMaster()->thenReturn(true);

        // mock a sentinel client that is temporarily offline
        $sentinelClient = \Phake::mock('\\PSRedis\\Client');
        \Phake::when($sentinelClient)->connect()
            ->thenThrow(
                new ConnectionError(sprintf('Could not connect to sentinel at %s:%d', $this->onlineSentinelIpAddress, $this->onlineSentinelPort))
            )
            ->thenReturn(null);
        \Phake::when($sentinelClient)->getMaster(\Phake::anyParameters())->thenReturn($masterNode);
        \Phake::when($sentinelClient)->getIpAddress()->thenReturn($this->onlineSentinelIpAddress);
        \Phake::when($sentinelClient)->getPort()->thenReturn($this->onlineSentinelPort);

        return $sentinelClient;
    }

    private function mockOnlineSentinelWithMasterSteppingDown()
    {
        $clientAdapter = new PredisClientAdapter(new MockedPredisClientCreatorWithNoMasterAddress(), Client::TYPE_SENTINEL);

        $masterNodeSteppingDown = \Phake::mock('\\PSRedis\Client');
        \Phake::when($masterNodeSteppingDown)->getIpAddress()->thenReturn($this->onlineSteppingDownMasterIpAddress);
        \Phake::when($masterNodeSteppingDown)->getPort()->thenReturn($this->onlineSteppingDownMasterPort);
        \Phake::when($masterNodeSteppingDown)->isMaster()->thenReturn(false);

        $masterNode = \Phake::mock('\\PSRedis\Client');
        \Phake::when($masterNode)->getIpAddress()->thenReturn($this->onlineMasterIpAddress);
        \Phake::when($masterNode)->getPort()->thenReturn($this->onlineMasterPort);
        \Phake::when($masterNode)->isMaster()->thenReturn(true);

        $sentinelClient = \Phake::mock('\\PSRedis\\Client');
        \Phake::when($sentinelClient)->connect()->thenReturn(null);
        \Phake::when($sentinelClient)->getIpAddress()->thenReturn($this->onlineSentinelIpAddress);
        \Phake::when($sentinelClient)->getPort()->thenReturn($this->onlineSentinelPort);
        \Phake::when($sentinelClient)->getClientAdapter()->thenReturn($clientAdapter);
        \Phake::when($sentinelClient)->getMaster(\Phake::anyParameters())
            ->thenReturn($masterNodeSteppingDown)
            ->thenReturn($masterNode);

        return $sentinelClient;
    }

    public function testAMonitorSetHasAName()
    {
        $masterDiscovery = new MasterDiscovery($this->masterName);
        $this->assertEquals($this->masterName, $masterDiscovery->getName(), 'A master discovery is identified by a name (sentinels can monitor more than 1 master)');
    }

    public function testAMonitorSetNameCannotBeEmpty()
    {
        $this->setExpectedException('\\PSRedis\\Exception\\InvalidProperty', 'A master discovery needs a valid name (sentinels can monitor more than 1 master)');
        new MasterDiscovery('');
    }

    public function testThatSentinelClientsCanBeAddedToMonitorSets()
    {
        $masterDiscovery = new MasterDiscovery($this->masterName);
        $masterDiscovery->addSentinel($this->mockOnlineSentinel());
        $this->assertAttributeCount(1, 'sentinels', $masterDiscovery, 'Sentinel node can be added to a master discovery object');
    }

    public function testThatOnlySentinelClientObjectsCanBeAddedAsNode()
    {
        $this->setExpectedException('\\PHPUnit_Framework_Error', 'Argument 1 passed to PSRedis\MasterDiscovery::addSentinel() must be an instance of PSRedis\Client');
        $masterDiscovery = new MasterDiscovery($this->masterName);
        $masterDiscovery->addSentinel(new \StdClass());
    }

    public function testThatWeNeedNodesConfigurationToDiscoverAMaster()
    {
        $this->setExpectedException('\\PSRedis\\Exception\\ConfigurationError', 'You need to configure and add sentinel nodes before attempting to fetch a master');
        $masterDiscovery = new MasterDiscovery($this->masterName);
        $masterDiscovery->getMaster();
    }

    public function testThatMasterCannotBeFoundIfWeCannotConnectToSentinels()
    {
        $this->setExpectedException('\\PSRedis\\Exception\\ConnectionError', 'All sentinels are unreachable');
        $sentinel1 = $this->mockOfflineSentinel();
        $sentinel2 = $this->mockOfflineSentinel();
        $masterDiscovery = new MasterDiscovery('all-fail');
        $masterDiscovery->addSentinel($sentinel1);
        $masterDiscovery->addSentinel($sentinel2);
        $masterDiscovery->getMaster();
    }

    public function testThatSentinelNodeIsReturnedOnSuccessfulMasterDiscovery()
    {
        $noBackoff = new Incremental(0, 1);
        $noBackoff->setMaxAttempts(1);

        $sentinel1 = $this->mockOfflineSentinel();
        $sentinel2 = $this->mockOnlineSentinel();

        $masterDiscovery = new MasterDiscovery('online-sentinel');
        $masterDiscovery->setBackoffStrategy($noBackoff);
        $masterDiscovery->addSentinel($sentinel1);
        $masterDiscovery->addSentinel($sentinel2);
        $masterNode = $masterDiscovery->getMaster();

        $this->assertInstanceOf('\\PSRedis\\Client', $masterNode, 'The master returned should be an instance of \\PSRedis\\Client');
        $this->assertEquals($this->onlineMasterIpAddress, $masterNode->getIpAddress(), 'The master node IP address returned should be the one of the online sentinel');
        $this->assertEquals($this->onlineMasterPort, $masterNode->getPort(), 'The master node IP port returned should be the one of the online sentinel');
    }

    public function testThatMasterStatusOfANodeIsCheckedAfterConnecting()
    {
        $this->setExpectedException('\\PSRedis\\Exception\\ConnectionError', 'All sentinels are unreachable');

        $sentinel1 = $this->mockOnlineSentinelWithMasterSteppingDown();
        $sentinel2 = $this->mockOnlineSentinel();
        $masterDiscovery = new MasterDiscovery('online-sentinel');
        $masterDiscovery->addSentinel($sentinel1);
        $masterDiscovery->addSentinel($sentinel2);
        $masterDiscovery->getMaster();
    }

    public function testThatABackoffIsAttempted()
    {
        $backoffOnce = new Incremental(0, 1);
        $backoffOnce->setMaxAttempts(2);

        $sentinel1 = $this->mockOfflineSentinel();
        $sentinel2 = $this->mockOnlineSentinelWithMasterSteppingDown();

        $masterDiscovery = new MasterDiscovery('online-sentinel');
        $masterDiscovery->setBackoffStrategy($backoffOnce);
        $masterDiscovery->addSentinel($sentinel1);
        $masterDiscovery->addSentinel($sentinel2);
        $masterNode = $masterDiscovery->getMaster();

        $this->assertEquals($this->onlineMasterIpAddress, $masterNode->getIpAddress(), 'A master that stepped down between discovery and connecting should be retried after backoff (check IP address)');
        $this->assertEquals($this->onlineMasterPort, $masterNode->getPort(), 'A master that stepped down between discovery and connecting should be retried after backoff (check port)');
    }

    public function testThatTheMasterHasTheCorrectRole()
    {
        $noBackoff = new Incremental(0, 1);
        $noBackoff->setMaxAttempts(1);

        $sentinel1 = $this->mockOfflineSentinel();
        $sentinel2 = $this->mockOnlineSentinel();

        $masterDiscovery = new MasterDiscovery('online-sentinel');
        $masterDiscovery->setBackoffStrategy($noBackoff);
        $masterDiscovery->addSentinel($sentinel1);
        $masterDiscovery->addSentinel($sentinel2);
        $masterNode = $masterDiscovery->getMaster();

        $this->assertEquals(Client::ROLE_MASTER, $masterNode->getRole(), 'The role of the master should be \'master\'');
    }

    public function testThatAnObserverIsCalledOnBackoff()
    {
        $this->observedBackoff = false;

        $backoffOnce = new Incremental(0, 1);
        $backoffOnce->setMaxAttempts(2);

        $sentinel1 = $this->mockOfflineSentinel();
        $sentinel2 = $this->mockOnlineSentinelWithMasterSteppingDown();

        $masterDiscovery = new MasterDiscovery('online-sentinel');
        $masterDiscovery->setBackoffStrategy($backoffOnce);
        $masterDiscovery->addSentinel($sentinel1);
        $masterDiscovery->addSentinel($sentinel2);
        $masterDiscovery->setBackoffObserver(array($this, 'backoffObserver'));
        $masterDiscovery->getMaster();

        $this->assertTrue($this->observedBackoff, 'When backing off an observer can be called');
    }

    public function backoffObserver()
    {
        $this->observedBackoff = true;
    }

    /**
     * @group regression
     * @group issue-9
     */
    public function testThatABackoffStrategyIsResetWhenStartingTheMasterDiscovery()
    {
        $backoff = new Incremental(0, 1);
        $backoff->setMaxAttempts(2);

        $sentinel1 = $this->mockOfflineSentinel();

        $masterDiscovery = new MasterDiscovery('online-sentinel');
        $masterDiscovery->setBackoffStrategy($backoff);
        $masterDiscovery->addSentinel($sentinel1);

        try {
            $masterNode = $masterDiscovery->getMaster();
        } catch (ConnectionError $e) {
            // we expect this to fail as no sentinels are online
        }

        // add a sentinel that fails first, but succeeds after back-off (the bug, if present, will prevent reconnection of sentinels after backoff)
        $sentinel2 = $this->mockTemporaryOfflineSentinel();
        $masterDiscovery->addSentinel($sentinel2);

        // try to discover the master node
        $masterNode = $masterDiscovery->getMaster();
        $this->assertInstanceOf('\\PSRedis\\Client', $masterNode, 'When backing off is reset on each discovery, we should have received the master node here');

    }
}
 