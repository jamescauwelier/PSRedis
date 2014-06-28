<?php


namespace RedisSentinel\RedisClient\Adapter;


use RedisSentinel\RedisClient;

class Null
    extends AbstractAdapter
    implements RedisClient\Adapter
{

    public function connect()
    {

    }

}