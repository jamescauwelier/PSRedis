<?php

namespace PSRedis\Client\Adapter;

use PSRedis\Client\ClientAdapter;
use PSRedis\Client;

/**
 * Class NullSentinelClientAdapter
 *
 * The null client is being used to test whether the sentinel client accepts multiple adapter by having another pretty
 * useless one that conforms to the SentinelClientAdapter interface.  As soon as we have another client library supported
 * we need to remove the null adapter again
 *
 * @package Sentinel\Client\Adapter
 */
class NullClientAdapter
    extends AbstractClientAdapter
    implements ClientAdapter
{

    public function connect()
    {
        $this->isConnected = true;
    }

    public function getMaster($nameOfNodeSet)
    {
        return new Client('127.0.0.1', 2020);
    }

    public function getRole()
    {
        return Client::ROLE_SENTINEL;
    }

    public function __call($methodName, array $methodParameters = array())
    {
        return null;
    }
}