<?php


namespace RedisSentinel\RedisClient\Adapter;

use RedisSentinel\RedisClient;

class Predis
    extends AbstractAdapter
    implements RedisClient\Adapter
{
    public function connect()
    {

    }

    public function isConnected()
    {
        return true;
    }
} 