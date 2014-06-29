<?php

namespace Redis\Client\Adapter;

use Redis\Client\Adapter\Predis\PredisClientFactory;
use Redis\Client\SentinelClientAdapter;

class PredisSentinelClientAdapter
    extends AbstractSentinelClientAdapter
    implements SentinelClientAdapter
{
    /**
     * @var \Predis\Client
     */
    private $predisClient;

    /**
     * @var \Redis\Client\Adapter\Predis\PredisClientFactory
     */
    private $predisClientFactory;

    public function __construct(PredisClientFactory $clientFactory)
    {
        $this->predisClientFactory = $clientFactory;
    }

    public function connect()
    {
        $this->predisClient = $this->predisClientFactory->createSentinelClient($this->getPredisClientParameters());
        $this->predisClient->connect();
    }

    private function getPredisClientParameters()
    {
        return array(
            'scheme'    => 'tcp',
            'host'      => $this->ipAddress,
            'port'      => $this->port,
        );
    }

    public function getMaster()
    {
        $masterRedisParameters = array();
        return $this->predisClientFactory->createRedisClient($masterRedisParameters);
    }
} 