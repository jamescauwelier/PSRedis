<?php


namespace PSRedis\Client\Adapter\Predis\Mock;


use PSRedis\Client\Adapter\Predis\Mock\AbstractMockedPredisClientCreator;
use PSRedis\Client\Adapter\Predis\PredisClientFactory;

class MockedPredisClientCreatorWithNoMasterAddress
    extends AbstractMockedPredisClientCreator
    implements PredisClientFactory
{
    public function createSentinelClient(array $parameters = array())
    {
        $mockedSentinelClient = \Phake::mock('\\Predis\\Client');
        return $mockedSentinelClient;
    }

    public function createRedisClient(array $parameters = array())
    {
        $mockedRedisClient = \Phake::mock('\\PSRedis\\Client');
        \Phake::when($mockedRedisClient)->isMaster()->thenReturn(false);
        \Phake::when($mockedRedisClient)->getMaster(\Phake::anyParameters())->thenReturn($mockedRedisClient);

        return $mockedRedisClient;
    }
} 