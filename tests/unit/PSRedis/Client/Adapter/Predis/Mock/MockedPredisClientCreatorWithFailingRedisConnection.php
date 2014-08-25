<?php


namespace PSRedis\Client\Adapter\Predis\Mock;

use PSRedis\Client\Adapter\Predis\PredisClientFactory;
use PSRedis\Client;

class MockedPredisClientCreatorWithFailingRedisConnection
    extends AbstractMockedPredisClientCreator
    implements PredisClientFactory
{
    public function createSentinelClient(array $parameters = array())
    {
        $mockedSentinelClient = \Phake::mock('\\Predis\\Client');
        \Phake::when($mockedSentinelClient)->sentinel(\Phake::anyParameters())->thenReturn(array('127.0.0.1', 2020));
        \Phake::when($mockedSentinelClient)->role()->thenReturn(Client::ROLE_SENTINEL);
        \Phake::when($mockedSentinelClient)->set('test', 'ok')->thenReturn(true);
        \Phake::when($mockedSentinelClient)->get('test')->thenReturn('ok');

        return $mockedSentinelClient;
    }

    public function createRedisClient(array $parameters = array())
    {
        $mockedConnectionException = \Phake::mock('Predis\\Connection\\ConnectionException');

        $mockedRedisClient = \Phake::mock('\\PSRedis\\Client');
        \Phake::when($mockedRedisClient)->isMaster()->thenReturn(false);
        \Phake::when($mockedRedisClient)->getMaster(\Phake::anyParameters())->thenReturn($mockedRedisClient);
        \Phake::when($mockedRedisClient)->set(\Phake::anyParameters())->thenThrow($mockedConnectionException);
        \Phake::when($mockedRedisClient)->get(\Phake::anyParameters())->thenThrow($mockedConnectionException);

        return $mockedRedisClient;
    }
} 