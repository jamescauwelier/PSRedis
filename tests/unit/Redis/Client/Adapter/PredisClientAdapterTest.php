<?php


namespace Redis\Client\Adapter;


use Redis\Client\Adapter\Predis\Mock\MockedPredisClientCreatorWithMasterAddress;
use Redis\Client\Adapter\Predis\Mock\MockedPredisClientCreatorWithNoMasterAddress;
use Redis\Client\Adapter\Predis\Mock\MockedPredisClientCreatorWithSentinelOffline;
use Redis\Client;

class PredisClientAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testThatAPredisClientIsCreatedOnConnect()
    {
        $clientAdapter = new PredisClientAdapter(new MockedPredisClientCreatorWithNoMasterAddress(), Client::TYPE_SENTINEL);
        $clientAdapter->setIpAddress('127.0.0.1');
        $clientAdapter->setPort(4545);
        $clientAdapter->connect();

        $this->assertAttributeInstanceOf('\\Predis\\Client', 'predisClient', $clientAdapter, 'The adapter should create and configure a \\Predis\\Client object');
    }

    public function testThatMasterIsOfCorrectType()
    {
        $clientAdapter = new PredisClientAdapter(new MockedPredisClientCreatorWithMasterAddress(), Client::TYPE_SENTINEL);
        $clientAdapter->setIpAddress('127.0.0.1');
        $clientAdapter->setPort(4545);
        $master = $clientAdapter->getMaster('test');

        $this->assertInstanceOf('\\Redis\\Client', $master, 'The master returned should be of type \\Redis\\Client');
    }

    public function testThatConnectionToAnOfflineSentinelThrowsAnException()
    {
        $this->setExpectedException('\\Redis\\Exception\\ConnectionError');

        $clientAdapter = new PredisClientAdapter(new MockedPredisClientCreatorWithSentinelOffline(), Client::TYPE_SENTINEL);
        $clientAdapter->setIpAddress('127.0.0.1');
        $clientAdapter->setPort(4545);
        $clientAdapter->connect();
    }

    public function testThatExceptionIsThrownWhenMasterIsUnknownToSentinel()
    {
        $this->setExpectedException('\\Redis\\Exception\\SentinelError', 'The sentinel does not know the master address');

        $clientAdapter = new PredisClientAdapter(new MockedPredisClientCreatorWithNoMasterAddress(), Client::TYPE_SENTINEL);
        $clientAdapter->setIpAddress('127.0.0.1');
        $clientAdapter->setPort(4545);
        $clientAdapter->getMaster('test');
    }

    public function testThatTheAdapterReturnsTheRoleOfTheServer()
    {
        $clientAdapter = new PredisClientAdapter(new MockedPredisClientCreatorWithMasterAddress(), Client::TYPE_SENTINEL);
        $clientAdapter->setIpAddress('127.0.0.1');
        $clientAdapter->setPort(4545);

        $this->assertEquals('sentinel', $clientAdapter->getRole(), 'The server we are connected to is a sentinel');
    }
}
 