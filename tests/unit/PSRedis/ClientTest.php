<?php

namespace PSRedis;

use PSRedis\Exception\ConnectionError;
use PSRedis\Client\Adapter\NullClientAdapter;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    private $ipAddress = '127.0.0.1';
    private $port = 2323;

    private function mockOfflineClientAdapter()
    {
        $redisClientAdapter = \Phake::mock('\\PSRedis\\Client\\Adapter\\PredisClientAdapter');
        \Phake::when($redisClientAdapter)->connect()->thenThrow(
            new ConnectionError(sprintf('Could not connect to sentinel at %s:%d', $this->ipAddress, $this->port))
        );
        \Phake::when($redisClientAdapter)->isConnected()->thenReturn(false);

        return $redisClientAdapter;
    }

    private function mockOnlineClientAdapter()
    {
        $redisClientAdapter = \Phake::mock('\\PSRedis\\Client\\Adapter\\PredisClientAdapter');
        \Phake::when($redisClientAdapter)->isConnected()->thenReturn(true);

        return $redisClientAdapter;
    }

    private function mockClientAdapterForMaster($masterClient = null)
    {
        return $this->mockClientAdapterForRole(Client::ROLE_MASTER, $masterClient);
    }

    private function mockClientAdapterForSlave($slaveClient = null)
    {
        return $this->mockClientAdapterForRole(Client::ROLE_SLAVE, $slaveClient);
    }

    private function mockClientAdapterForSentinel($sentinelClient = null)
    {
        return $this->mockClientAdapterForRole(Client::ROLE_SENTINEL, $sentinelClient);
    }

    private function mockClientAdapterForRole($role, $client = null)
    {
        if (empty($client)) {
            $client = \Phake::mock('\\PSRedis\\Client');
        }

        $redisClientAdapter = \Phake::mock('\\PSRedis\\Client\\Adapter\\PredisClientAdapter');
        \Phake::when($redisClientAdapter)->isConnected()->thenReturn(true);
        \Phake::when($redisClientAdapter)->getMaster('test')->thenReturn($client);
        \Phake::when($redisClientAdapter)->getRole()->thenReturn(array($role));
        \Phake::when($redisClientAdapter)->set('test', 'ok')->thenReturn(true);
        \Phake::when($redisClientAdapter)->get('test')->thenReturn('ok');

        return $redisClientAdapter;
    }

    public function testSentinelHasIpAddress()
    {
        $sentinel = new Client($this->ipAddress, $this->port);
        $this->assertEquals($this->ipAddress, $sentinel->getIpAddress(), 'A sentinel location is identified by ip address');
    }

    public function testSentinelHasPort()
    {
        $sentinel = new Client($this->ipAddress, $this->port);
        $this->assertEquals($this->port, $sentinel->getPort(), 'A sentinel location needs a port to be identifiable');
    }

    public function testSentinelHasPredisAsStandardAdapter()
    {
        $sentinel = new Client($this->ipAddress, $this->port);
        $this->assertAttributeInstanceOf('\\PSRedis\\Client\\Adapter\\PredisClientAdapter', 'clientAdapter', $sentinel, 'By default, the library uses predis to make connection with redis');
    }

    public function testSentinelAcceptsOtherAdapters()
    {
        $sentinel = new Client($this->ipAddress, $this->port, new NullClientAdapter());
        $this->assertAttributeInstanceOf('\\PSRedis\\Client\\Adapter\\NullClientAdapter', 'clientAdapter', $sentinel, 'The used redis client adapter can be swapped');
    }

    public function testThatFailureToConnectToSentinelsThrowsAnError()
    {
        $this->setExpectedException('\\PSRedis\\Exception\ConnectionError', sprintf('Could not connect to sentinel at %s:%d', $this->ipAddress, $this->port));

        $sentinelNode = new Client($this->ipAddress, $this->port, $this->mockOfflineClientAdapter());
        $sentinelNode->connect();
    }

    public function testThatBeforeConnectingSentinelNodesKnowTheirConnectionState()
    {
        $sentinelNode = new Client($this->ipAddress, $this->port, $this->mockOfflineClientAdapter());
        $this->assertFalse($sentinelNode->isConnected(), 'A new sentinel code object is not connected');
    }

    public function testThatAfterAFailedConnectionAttemptSentinelNodesKnowTheirConnectionState()
    {
        $sentinelNode = new Client($this->ipAddress, $this->port, $this->mockOfflineClientAdapter());
        try {
            $sentinelNode->connect();
        } catch (ConnectionError $e) {

        }
        $this->assertFalse($sentinelNode->isConnected(), 'After a failed connection attempt, the connection state should be bool(false)');
    }

    public function testThatAfterASuccessfullConnectionTheSentinelsKnowsTheirConnectionState()
    {
        $sentinelNode = new Client($this->ipAddress, $this->port, $this->mockOnlineClientAdapter());
        $sentinelNode->connect();
        $this->assertTrue($sentinelNode->isConnected(), 'After a successfull connection attempt, the connection state is bool(true)');
    }

    public function testThatWeCanGetTheClientAdapter()
    {
        $onlineClientAdapter = $this->mockOnlineClientAdapter();
        $sentinelNode = new Client($this->ipAddress, $this->port, $onlineClientAdapter);
        $this->assertEquals($onlineClientAdapter, $sentinelNode->getClientAdapter(), 'A sentinel can return the client adapter');
    }

    public function testThatTheMasterReturnedComesFromClientAdapter()
    {
        $masterClient = \Phake::mock('\\PSRedis\\Client');
        $masterClientAdapter = $this->mockClientAdapterForMaster($masterClient);
        $masterNode = new Client($this->ipAddress, $this->port, $masterClientAdapter);
        $this->assertEquals($masterClient, $masterNode->getMaster('test'), 'The redis client gets the master object from the client adapter');
    }

    public function testThatTheRoleReturnedComesFromClientAdapter()
    {
        $masterClientAdapter = $this->mockClientAdapterForMaster();
        $masterNode = new Client($this->ipAddress, $this->port, $masterClientAdapter);
        $this->assertEquals(array(Client::ROLE_MASTER), $masterNode->getRole(), 'The role of the node is provided by the client adapter');
    }

    public function testThatTheRoleTypeReturnedComesFromClientAdapter()
    {
        $masterClientAdapter = $this->mockClientAdapterForMaster();
        $masterNode = new Client($this->ipAddress, $this->port, $masterClientAdapter);
        $this->assertEquals(Client::ROLE_MASTER, $masterNode->getRoleType(), 'The type of role of the node is provided by the client adapter');
    }

    public function testThatAMasterIsBeingIdentifiedAsOne()
    {
        $masterClientAdapter = $this->mockClientAdapterForMaster();
        $masterNode = new Client($this->ipAddress, $this->port, $masterClientAdapter);
        $this->assertTrue($masterNode->isMaster(), 'A master should be identified as master');
        $this->assertFalse($masterNode->isSlave(), 'A master should not be identified as slave');
        $this->assertFalse($masterNode->isSentinel(), 'A master should not be identified as sentinel');
    }

    public function testThatASentinelIsBeingIdentifiedAsOne()
    {
        $sentinelClientAdapter = $this->mockClientAdapterForSentinel();
        $sentinelNode = new Client($this->ipAddress, $this->port, $sentinelClientAdapter);
        $this->assertTrue($sentinelNode->isSentinel(), 'A sentinel should be identified as sentinel');
        $this->assertFalse($sentinelNode->isSlave(), 'A sentinel should not be identified as slave');
        $this->assertFalse($sentinelNode->isMaster(), 'A sentinel should not be identified as master');
    }

    public function testThatASlaveIsBeingIdentifiedAsOne()
    {
        $slaveClientAdapter = $this->mockClientAdapterForSlave();
        $slaveNode = new Client($this->ipAddress, $this->port, $slaveClientAdapter);
        $this->assertTrue($slaveNode->isSlave(), 'A slave should be identified as slave');
        $this->assertFalse($slaveNode->isSentinel(), 'A slave should not be identified as sentinel');
        $this->assertFalse($slaveNode->isMaster(), 'A slave should not be identified as master');
    }

    public function testThatRedisCommandsAreProxied()
    {
        $masterClientAdapter = $this->mockClientAdapterForMaster();
        $masterNode = new Client($this->ipAddress, $this->port, $masterClientAdapter);
        $this->assertTrue($masterNode->set('test', 'ok'), 'SET command is proxied');
        $this->assertEquals('ok', $masterNode->get('test'), 'GET command is proxied');
    }
}
 