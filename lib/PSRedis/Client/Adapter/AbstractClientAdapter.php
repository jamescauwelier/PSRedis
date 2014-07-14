<?php

namespace PSRedis\Client\Adapter;

/**
 * Class AbstractClientAdapter
 *
 * Common functionality to other client adapters.
 *
 * @package PSRedis\Client\Adapter
 */
abstract class AbstractClientAdapter
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