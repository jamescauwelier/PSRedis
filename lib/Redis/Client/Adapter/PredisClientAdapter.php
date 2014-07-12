<?php

namespace Redis\Client\Adapter;

use Redis\Client\Adapter\Predis\Command\SentinelCommand;
use Redis\Client\Adapter\Predis\PredisClientFactory;
use Redis\Client\ClientAdapter;
use Redis\Client;
use Redis\Exception\SentinelError;

class PredisClientAdapter
    extends AbstractClientAdapter
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

    private $clientType;

    public function __construct(PredisClientFactory $clientFactory, $clientType)
    {
        $this->predisClientFactory = $clientFactory;
        $this->clientType = $clientType;
    }

    /**
     * @return \Predis\Client
     */
    private function getPredisClient()
    {
        if (empty($this->predisClient)) {
            $this->connect();
        }

        return $this->predisClient;
    }

    public function connect()
    {
        $this->predisClient = $this->predisClientFactory->createClient($this->clientType, $this->getPredisClientParameters());
        $this->predisClient->connect();
        $this->isConnected = $this->predisClient->isConnected();
    }

    private function getPredisClientParameters()
    {
        return array(
            'scheme'    => 'tcp',
            'host'      => $this->ipAddress,
            'port'      => $this->port,
        );
    }

    public function getMaster($nameOfNodeSet)
    {
        list($masterIpAddress, $masterPort) = $this->getPredisClient()->sentinel(SentinelCommand::GETMASTER, $nameOfNodeSet);

        if (!empty($masterIpAddress) AND !empty($masterPort)) {
            return new \Redis\Client($masterIpAddress, $masterPort, new PredisClientAdapter($this->predisClientFactory, Client::TYPE_REDIS));
        }

        throw new SentinelError('The sentinel does not know the master address');
    }

    public function getRole()
    {
        return $this->getPredisClient()->role();
    }
}