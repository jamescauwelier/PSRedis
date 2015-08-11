<?php

namespace PSRedis\NodeDiscovery;

use PSRedis\Client\ClientAdapter;
use PSRedis\Sentinel\Configuration;

/**
 * Interface for implementing various strategies of finding a node in a sentinel group
 */
interface NodeDiscoveryStrategy
{
    /**
     * @param Configuration $sentinelConfiguration
     * @return ClientAdapter
     */
    public function getNode(Configuration $sentinelConfiguration);
}