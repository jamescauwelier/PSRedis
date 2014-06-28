<?php

namespace RedisSentinel;

use RedisSentinel\Exception\ConnectionError;
use RedisSentinel\RedisClient\Adapter\Null;

class SentinelNodeTest extends \PHPUnit_Framework_TestCase
{
    private $ipAddress = '127.0.0.1';
    private $port = 2323;

    private function mockOfflineRedisClientAdapter()
    {
        $redisClientAdapter = \Phake::mock('\\RedisSentinel\\RedisClient\\Adapter\\Predis');
        \Phake::when($redisClientAdapter)->connect()->thenThrow(
            new ConnectionError(sprintf('Could not connect to sentinel at %s:%d', $this->ipAddress, $this->port))
        );
        \Phake::when($redisClientAdapter)->isConnected()->thenReturn(false);

        return $redisClientAdapter;
    }

    private function mockOnlineRedisClientAdapter()
    {
        $redisClientAdapter = \Phake::mock('\\RedisSentinel\\RedisClient\\Adapter\\Predis');
        \Phake::when($redisClientAdapter)->isConnected()->thenReturn(true);

        return $redisClientAdapter;
    }

    public function testSentinelHasIpAddress()
    {
        $sentinel = new SentinelNode($this->ipAddress, $this->port);
        $this->assertEquals($this->ipAddress, $sentinel->getIpAddress(), 'A sentinel location is identified by ip address');
    }

    public function testSentinelRequiresAValidIpAddress()
    {
        $this->setExpectedException('\\RedisSentinel\\Exception\\InvalidProperty', 'A sentinel node requires a valid IP address');
        new SentinelNode('blabla', $this->port);
    }

    public function testSentinelHasPort()
    {
        $sentinel = new SentinelNode($this->ipAddress, $this->port);
        $this->assertEquals($this->port, $sentinel->getPort(), 'A sentinel location needs a port to be identifiable');
    }

    public function testSentinelHasPredisAsStandardAdapter()
    {
        $sentinel = new SentinelNode($this->ipAddress, $this->port);
        $this->assertAttributeInstanceOf('\\RedisSentinel\\RedisClient\\Adapter\\Predis', 'redisClientAdapter', $sentinel, 'By default, the library uses predis to make connection with redis');
    }

    public function testSentinelAcceptsOtherAdapters()
    {
        $sentinel = new SentinelNode($this->ipAddress, $this->port, new Null());
        $this->assertAttributeInstanceOf('\\RedisSentinel\\RedisClient\\Adapter\\Null', 'redisClientAdapter', $sentinel, 'The used redis client adapter can be swapped');
    }

    public function testSentinelRefusesTextAsAnInvalidPort()
    {
        $this->setExpectedException('\\RedisSentinel\\Exception\\InvalidProperty', 'A sentinel node requires a valid service port');
        new SentinelNode($this->ipAddress, 'abc');
    }

    public function testThatFailureToConnectToSentinelsThrowsAnError()
    {
        $this->setExpectedException('\\RedisSentinel\\Exception\ConnectionError', sprintf('Could not connect to sentinel at %s:%d', $this->ipAddress, $this->port));

        $sentinelNode = new SentinelNode($this->ipAddress, $this->port, $this->mockOfflineRedisClientAdapter());
        $sentinelNode->connect();
    }

    public function testThatBeforeConnectingSentinelNodesKnowTheirConnectionState()
    {
        $sentinelNode = new SentinelNode($this->ipAddress, $this->port, $this->mockOfflineRedisClientAdapter());
        $this->assertFalse($sentinelNode->isConnected(), 'A new sentinel code object is not connected');
    }

    public function testThatAfterAFailedConnectionAttemptSentinelNodesKnowTheirConnectionState()
    {
        $sentinelNode = new SentinelNode($this->ipAddress, $this->port, $this->mockOfflineRedisClientAdapter());
        try {
            $sentinelNode->connect();
        } catch (ConnectionError $e) {

        }
        $this->assertFalse($sentinelNode->isConnected(), 'After a failed connection attempt, the connection state should be bool(false)');
    }

    public function testThatAfterASuccessfullConnectionTheSentinelsKnowsTheirConnectionState()
    {
        $sentinelNode = new SentinelNode($this->ipAddress, $this->port, $this->mockOnlineRedisClientAdapter());
        $sentinelNode->connect();
        $this->assertTrue($sentinelNode->isConnected(), 'After a successfull connection attempt, the connection state is bool(true)');
    }
}
 