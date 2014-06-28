<?php

namespace Sentinel;

use Sentinel\Exception\ConnectionError;
use Sentinel\Client\Adapter\NullSentinelClientAdapter;

class SentinelNodeTest extends \PHPUnit_Framework_TestCase
{
    private $ipAddress = '127.0.0.1';
    private $port = 2323;

    private function mockOfflineClientAdapter()
    {
        $redisClientAdapter = \Phake::mock('\\Sentinel\\Client\\Adapter\\PredisSentinelClientAdapter');
        \Phake::when($redisClientAdapter)->connect()->thenThrow(
            new ConnectionError(sprintf('Could not connect to sentinel at %s:%d', $this->ipAddress, $this->port))
        );
        \Phake::when($redisClientAdapter)->isConnected()->thenReturn(false);

        return $redisClientAdapter;
    }

    private function mockOnlineClientAdapter()
    {
        $redisClientAdapter = \Phake::mock('\\Sentinel\\Client\\Adapter\\PredisSentinelClientAdapter');
        \Phake::when($redisClientAdapter)->isConnected()->thenReturn(true);

        return $redisClientAdapter;
    }

    public function testSentinelHasIpAddress()
    {
        $sentinel = new Client($this->ipAddress, $this->port);
        $this->assertEquals($this->ipAddress, $sentinel->getIpAddress(), 'A sentinel location is identified by ip address');
    }

    public function testSentinelRequiresAValidIpAddress()
    {
        $this->setExpectedException('\\Sentinel\\Exception\\InvalidProperty', 'A sentinel node requires a valid IP address');
        new Client('blabla', $this->port);
    }

    public function testSentinelHasPort()
    {
        $sentinel = new Client($this->ipAddress, $this->port);
        $this->assertEquals($this->port, $sentinel->getPort(), 'A sentinel location needs a port to be identifiable');
    }

    public function testSentinelHasPredisAsStandardAdapter()
    {
        $sentinel = new Client($this->ipAddress, $this->port);
        $this->assertAttributeInstanceOf('\\Sentinel\\Client\\Adapter\\PredisSentinelClientAdapter', 'clientAdapter', $sentinel, 'By default, the library uses predis to make connection with redis');
    }

    public function testSentinelAcceptsOtherAdapters()
    {
        $sentinel = new Client($this->ipAddress, $this->port, new NullSentinelClientAdapter());
        $this->assertAttributeInstanceOf('\\Sentinel\\Client\\Adapter\\NullSentinelClientAdapter', 'clientAdapter', $sentinel, 'The used redis client adapter can be swapped');
    }

    public function testSentinelRefusesTextAsAnInvalidPort()
    {
        $this->setExpectedException('\\Sentinel\\Exception\\InvalidProperty', 'A sentinel node requires a valid service port');
        new Client($this->ipAddress, 'abc');
    }

    public function testThatFailureToConnectToSentinelsThrowsAnError()
    {
        $this->setExpectedException('\\Sentinel\\Exception\ConnectionError', sprintf('Could not connect to sentinel at %s:%d', $this->ipAddress, $this->port));

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
}
 