<?php

namespace PSRedis\Sentinel;

use PSRedis\Client;

/**
 * Configuration of the Sentinel environment
 */
class Configuration
{
    /**
     * @var array Array of sentinel clients
     */
    private $sentinelClients = array();

    /**
     * @param Client $sentinelClient
     */
    public function addSentinel(Client $sentinelClient)
    {
        $this->sentinelClients[] = $sentinelClient;
    }

    /**
     * @return \SplFixedArray
     */
    public function getSentinels()
    {
        return \SplFixedArray::fromArray($this->sentinelClients);
    }
}