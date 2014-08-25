<?php

namespace PSRedis;

use PSRedis\MasterDiscovery\BackoffStrategy\None;
use PSRedis\MasterDiscovery\BackoffStrategy;
use PSRedis\Exception\ConfigurationError;
use PSRedis\Exception\ConnectionError;
use PSRedis\Exception\InvalidProperty;
use PSRedis\Exception\RoleError;
use PSRedis\Exception\SentinelError;

/**
 * Class MasterDiscovery
 *
 * Implements the logic to discover a master by connecting to and questioning a collection of sentinel clients
 *
 * @package PSRedis
 * @see http://redis.io/topics/sentinel-clients Official client requirements. Explains the different steps taken in master discovery.
 */
class MasterDiscovery
{
    /**
     * The name of the set consisting of 1 master and  1 or more replicated slaves
     * @var string
     */
    private $name;

    /**
     * The collection of sentinels to use when trying to discover the current master node
     * @var Client[]
     */
    private $sentinels = array();

    /**
     * The strategy to use when none of the sentinels could be reached.  Should we try again or leave it at that?
     * @var MasterDiscovery\BackoffStrategy\None
     */
    private $backoffStrategy;

    /**
     * The callable to be called when backing off during master discovery.  To be used for logging and making the
     * code testable (See integration tests)
     * @var callable
     */
    private $backoffObserver;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->guardThatTheNameIsNotBlank($name);
        $this->name = $name;

        // by default we don't implement a backoff
        $this->backoffStrategy = new None();
    }

    /**
     * @param BackoffStrategy $backoffStrategy
     */
    public function setBackoffStrategy(BackoffStrategy $backoffStrategy)
    {
        $this->backoffStrategy = $backoffStrategy;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param Client $sentinelClient
     */
    public function addSentinel(Client $sentinelClient)
    {
        $this->sentinels[] = $sentinelClient;
    }

    /**
     * @return \SplFixedArray
     */
    public function getSentinels()
    {
        return \SplFixedArray::fromArray($this->sentinels);
    }

    /**
     * Validation method for the name of the sentinels and redis collection
     * @param $name
     * @throws Exception\InvalidProperty
     */
    private function guardThatTheNameIsNotBlank($name)
    {
        if (empty($name)) {
            throw new InvalidProperty('A master discovery needs a valid name (sentinels can monitor more than 1 master)');
        }
    }

    /**
     * Actual discovery logic to find out the IP and port of the master node
     * @return Client\ClientAdapter
     * @throws Exception\ConnectionError
     * @throws Exception\ConfigurationError
     */
    public function getMaster()
    {
        if ($this->getSentinels()->count() == 0) {
            throw new ConfigurationError('You need to configure and add sentinel nodes before attempting to fetch a master');
        }

        $this->backoffStrategy->reset();

        do {

            try {

                foreach ($this->getSentinels() as $sentinelClient) {
                    /** @var $sentinelClient Client */
                    try {
                        $sentinelClient->connect();
                        $redisClient = $sentinelClient->getMaster($this->getName());
                        if (!empty($redisClient) AND $redisClient->isMaster()) {
                            return $redisClient;
                        } else {
                            throw new RoleError('Only a node with role master may be returned (maybe the master was stepping down during connection?)');
                        }
                    } catch (ConnectionError $e) {
                        // on error, try to connect to next sentinel
                    } catch (SentinelError $e) {
                        // when the sentinel throws an error, we try the next sentinel in the set
                    }
                }
            } catch (RoleError $e) {
                // if the role of the node isn't what we expected it to be, then we assume the master was not found and we rely on backoff mechanisms
                // we don't even try with the next sentinel, but pauze discovery altogether
            }

            if ($this->backoffStrategy->shouldWeTryAgain()) {
                $backoffInMicroseconds = $this->backoffStrategy->getBackoffInMicroSeconds();
                if (!empty($this->backoffObserver)) {
                    call_user_func($this->backoffObserver, $backoffInMicroseconds);
                }
                usleep($backoffInMicroseconds);
            }

        } while ($this->backoffStrategy->shouldWeTryAgain());

        throw new ConnectionError('All sentinels are unreachable');
    }

    /**
     * @param callable $observer
     */
    public function setBackoffObserver (callable $observer)
    {
        $this->backoffObserver = $observer;
    }
} 