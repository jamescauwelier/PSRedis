<?php


namespace PSRedis\Client\Adapter\Predis\Mock;


use PSRedis\Client\Adapter\Predis\PredisClientFactory;
use PSRedis\Client;

class MockedPredisClientCreatorWithMasterAddress
    extends AbstractMockedPredisClientCreator
    implements PredisClientFactory
{
    public function createSentinelClient(array $parameters = array())
    {
        $mockedSentinelClient = \Phake::mock('\\Predis\\Client');
        \Phake::when($mockedSentinelClient)->sentinel(\Phake::anyParameters())->thenReturn(array('127.0.0.1', 2020));
        \Phake::when($mockedSentinelClient)->role()->thenReturn(Client::ROLE_SENTINEL);
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