<?php

namespace PSRedis\NodeDiscovery;

use PSRedis\Exception\SentinelError;
use PSRedis\MasterDiscovery\BackoffStrategy;
use PSRedis\Exception\RoleError;
use PSRedis\Sentinel\Configuration;

/**
 * Class MasterDiscovery
 *
 * Implements the logic to discover a master by connecting to and questioning a collection of sentinel clients
 *
 * @package PSRedis
 * @see http://redis.io/topics/sentinel-clients Official client requirements. Explains the different steps taken in master discovery.
 */
class MasterDiscovery implements NodeDiscoveryStrategy
{
    public function getNode(Configuration $sentinelConfiguration)
    {
        foreach ($sentinelConfiguration->getSentinelConnections() as $sentinelConnection) {
            $redisClient = $sentinelConnection->getMaster($this->getName());
            if (!empty($redisClient) AND $redisClient->isMaster()) {
                return $redisClient;
            } else {
                throw new RoleError('Only a node with role master may be returned (maybe the master was stepping down during connection?)');
            }
        }

        throw new SentinelError('No master node found');
    }
} 