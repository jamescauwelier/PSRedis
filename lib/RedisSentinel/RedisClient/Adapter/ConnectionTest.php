<?php


namespace RedisSentinel\RedisClient\Adapter;


use RedisSentinel\RedisClient;

class ConnectionTest
    extends AbstractAdapter
    implements RedisClient\Adapter
{
    private $isConnected = false;

    public function connect()
    {
        $this->isConnected = true;
    }

    public function isConnected()
    {
        return $this->isConnected;
    }
} 