<?php

namespace Redis;

use Redis\Client\BackoffStrategy\None;
use Redis\Client\BackoffStrategy;
use Redis\Exception\ConfigurationError;
use Redis\Exception\ConnectionError;
use Redis\Exception\InvalidProperty;
use Redis\Exception\RoleError;
use Redis\Exception\SentinelError;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;

/**
 * Class MasterDiscovery
 *
 * Implements the logic to discover a master by making us of a collection of sentinel clients.
 *
 * @package Sentinel
 */
class MasterDiscovery
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Client[]
     */
    private $sentinels = array();

    /**
     * @var Client\BackoffStrategy\None
     */
    private $backoffStrategy;

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

    public function addSentinel(Client $sentinelClient)
    {
        $this->sentinels[] = $sentinelClient;
    }

    public function getSentinels()
    {
        return \SplFixedArray::fromArray($this->sentinels);
    }

    /**
     * @param $name
     * @throws Exception\InvalidProperty
     */
    private function guardThatTheNameIsNotBlank($name)
    {
        $validator = Validation::createValidator();
        $violations = $validator->validateValue($name, new NotBlank());
        if ($violations->count() > 0) {
            throw new InvalidProperty('A monitor set needs a valid name');
        }
    }

    /**
     * @return Client\ClientAdapter
     * @throws Exception\ConnectionError
     * @throws Exception\ConfigurationError
     */
    public function getMaster()
    {
        if ($this->getSentinels()->count() == 0) {
            throw new ConfigurationError('You need to configure and add sentinel nodes before attempting to fetch a master');
        }

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
            }

            if ($this->backoffStrategy->shouldWeTryAgain()) {
                usleep($this->backoffStrategy->getBackoffInMicroSeconds());
            }

        } while ($this->backoffStrategy->shouldWeTryAgain());

        throw new ConnectionError('All sentinels are unreachable');
    }
} 