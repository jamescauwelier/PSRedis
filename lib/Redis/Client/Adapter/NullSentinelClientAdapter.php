<?php

namespace Redis\Client\Adapter;

use Redis\Client\SentinelClientAdapter;

/**
 * Class NullSentinelClientAdapter
 *
 * The null client is being used to test whether the sentinel client accepts multiple adapter by having another pretty
 * useless one that conforms to the SentinelClientAdapter interface.  As soon as we have another client library supported
 * we need to remove the null adapter again
 *
 * @package Sentinel\Client\Adapter
 */
class NullSentinelClientAdapter
    extends AbstractSentinelClientAdapter
    implements SentinelClientAdapter
{

    public function connect()
    {
        $this->isConnected = true;
    }

    public function getMaster()
    {
        return new \StdClass();
    }
}