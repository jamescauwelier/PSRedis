<?php

namespace Sentinel\Client\Adapter;

abstract class AbstractSentinelClientAdapter
{
    protected $ipAddress;

    protected $port;

    protected $isConnected = false;

    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;
    }

    public function setPort($port)
    {
        $this->port = $port;
    }

    public function isConnected()
    {
        return $this->isConnected;
    }
} 