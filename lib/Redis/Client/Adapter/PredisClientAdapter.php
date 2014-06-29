<?php

namespace Redis\Client\Adapter;

use Redis\Client\Adapter\Predis\PredisClientFactory;
use Redis\Client\ClientAdapter;

class PredisClientAdapter
    extends AbstractSentinelClientAdapter
    implements ClientAdapter
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
        return new \Redis\Client($this->ipAddress, $this->port, new PredisClientAdapter($this->predisClientFactory));
    }
} 