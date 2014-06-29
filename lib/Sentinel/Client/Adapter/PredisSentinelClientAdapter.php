<?php

namespace Sentinel\Client\Adapter;

use Sentinel\Client\Adapter\Predis\Command\GetMasterAddressCommand;
use Sentinel\Client\Adapter\Predis\Command\RoleCommand;
use Sentinel\Client\SentinelClientAdapter;

class PredisSentinelClientAdapter
    extends AbstractSentinelClientAdapter
    implements SentinelClientAdapter
{
    /**
     * @var \Predis\Client
     */
    private $predisClient;

    public function connect()
    {
        $this->predisClient = new \Predis\Client($this->getPredisClientParameters());
        $this->predisClient->getProfile()->createCommand(new GetMasterAddressCommand());
        $this->predisClient->getProfile()->createCommand(new RoleCommand());
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