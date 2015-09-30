<?php

namespace PSRedis\Sentinel;

use PSRedis\Client;
use PSRedis\Exception\ConfigurationError;
use PSRedis\MasterDiscovery\BackoffStrategy;

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
     * The name of the set consisting of 1 master and  1 or more replicated slaves
     * @var string
     */
    private $name;

    /**
     * @param Client $sentinelClient
     */
    public function addSentinel(Client $sentinelClient)
    {
        $this->sentinelClients[] = $sentinelClient;
    }

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->guardThatTheNameIsNotBlank($name);
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Validation method for the name of the sentinels and redis collection
     * @param $name
     * @throws InvalidProperty
     */
    private function guardThatTheNameIsNotBlank($name)
    {
        if (empty($name)) {
            throw new InvalidProperty('A master discovery needs a valid name (sentinels can monitor more than 1 master)');
        }
    }

    /**
     * @return \SplFixedArray
     */
    public function getSentinels()
    {
        return \SplFixedArray::fromArray($this->sentinelClients);
    }

    public function getSentinelConnections()
    {
        if ($this->getSentinels()->count() == 0) {
            throw new ConfigurationError('You need to configure and add sentinel nodes before attempting to fetch a master');
        }

        foreach ($this->getSentinels() as $sentinelClient) {
            /** @var $sentinelClient Client */
            try {
                $sentinelClient->connect();
                yield $sentinelClient;
            } catch (ConnectionError $e) {
                // on error, try to connect to next sentinel
            } catch (SentinelError $e) {
                // when the sentinel throws an error, we try the next sentinel in the set
            }
        }

        throw new ConnectionError('All sentinels are unreachable');
    }
}