<?php


namespace Sentinel\Client\Adapter\Predis;


class MockedPredisClientCreator
    implements PredisClientFactory
{
    /**
     * @var \Predis\Client
     */
    private $mockObject;

    public function __construct()
    {
        $this->mockObject = \Phake::mock('\\Predis\\Client');
    }

    public function create(array $parameters = array())
    {
        return $this->mockObject;
    }
} 