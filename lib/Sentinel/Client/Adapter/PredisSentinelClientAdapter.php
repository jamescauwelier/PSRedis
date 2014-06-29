<?php

namespace Sentinel\Client\Adapter;

use Sentinel\Client\Adapter\Predis\PredisClientFactory;
use Sentinel\Client\SentinelClientAdapter;

class PredisSentinelClientAdapter
    extends AbstractSentinelClientAdapter
    implements SentinelClientAdapter
{
    /**
     * @var \Predis\Client
     */
    private $predisClient;

    /**
     * @var Predis\PredisClientFactory
     */
    private $predisClientFactory;

    public function __construct(PredisClientFactory $clientFactory)
    {
        $this->predisClientFactory = $clientFactory;
    }

    public function connect()
    {
        $this->predisClient = $this->predisClientFactory->create($this->getPredisClientParameters());
    }

    private function getPredisClientParameters()
    {
        return array(
            'scheme'    => 'tcp',
            'host'      => $this->ipAddress,
            'port'      => $this->port,
        );
    }
} 