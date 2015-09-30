<?php
namespace PSRedis\NodeDiscovery\Slave;

use PSRedis\Client\ClientAdapter;
use PSRedis\NodeDiscovery\NodeDiscoveryStrategy;
use PSRedis\Sentinel\Configuration;

/**
 * Finds a random slave node among your replica's
 */
class Random implements NodeDiscoveryStrategy
{

    /**
     * @param Configuration $sentinelConfiguration
     * @return ClientAdapter
     */
    public function getNode(Configuration $sentinelConfiguration)
    {
        // TODO: Implement getNode() method.
    }
}