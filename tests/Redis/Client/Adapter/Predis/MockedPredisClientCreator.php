<?php


namespace Redis\Client\Adapter\Predis;


class MockedPredisClientCreator
    implements PredisClientFactory
{
    /**
     * @var \Predis\Client
     */
    private $mockedSentinelClient;

    /**
     * @var \Predis\Client
     */
    private $mockedRedisClient;

    public function __construct()
    {
        $this->mockedSentinelClient = \Phake::mock('\\Predis\\Client');
        $this->mockedRedisClient = \Phake::mock('\\Redis\\Client');
    }

    public function createSentinelClient(array $parameters = array())
    {
        $mockedSentinelClient = \Phake::mock('\\Predis\\Client');
        return $mockedSentinelClient;
    }

    public function createRedisClient(array $parameters = array(), $isMaster = false)
    {
        $mockedRedisClient = \Phake::mock('\\Redis\\Client');
        \Phake::when($mockedRedisClient)->isMaster()->thenReturn($isMaster);

        return $mockedRedisClient;
    }
} 