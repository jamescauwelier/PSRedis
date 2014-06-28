<?php


namespace RedisSentinel\RedisClient\Adapter;


abstract class AbstractAdapter
{
    protected $ipAddress;

    protected $port;

    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;
    }

    public function setPort($port)
    {
        $this->port = $port;
    }
} 