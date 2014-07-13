<?php


namespace Redis\Client\Adapter\Predis\Mock;


use Redis\Client\Adapter\Predis\PredisClientFactory;
use Redis\Client;

class MockedPredisClientCreatorWithSentinelOffline
    extends AbstractMockedPredisClientCreator
    implements PredisClientFactory
{
    public function createSentinelClient(array $parameters = array())
    {
        $mockedSentinelClient = \Phake::mock('\\Predis\\Client');
        \Phake::when($mockedSentinelClient)->connect()->thenThrow(\Phake::mock('\\Predis\\Connection\\ConnectionException'));
        return $mockedSentinelClient;
    }

    public function createRedisClient(array $parameters = array())
    {
        $mockedRedisClient = \Phake::mock('\\Redis\\Client');
        \Phake::when($mockedRedisClient)->isMaster()->thenReturn(false);
        \Phake::when($mockedRedisClient)->getMaster(\Phake::anyParameters())->thenReturn($mockedRedisClient);

        return $mockedRedisClient;
    }
} 