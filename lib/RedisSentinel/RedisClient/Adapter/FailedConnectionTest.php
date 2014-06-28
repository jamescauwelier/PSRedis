<?php


namespace RedisSentinel\RedisClient\Adapter;


use RedisSentinel\Exception\ConnectionError;
use RedisSentinel\RedisClient;

class FailedConnectionTest
    extends AbstractAdapter
    implements RedisClient\Adapter
{
    public function connect()
    {
        throw new ConnectionError(sprintf('Could not connect to sentinel at %s:%d', $this->ipAddress, $this->port));
    }

    public function isConnected()
    {
        return false;
    }
} 