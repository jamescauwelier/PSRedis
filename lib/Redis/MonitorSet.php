<?php

namespace Redis;

use Redis\Exception\ConfigurationError;
use Redis\Exception\ConnectionError;
use Redis\Exception\InvalidProperty;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;

/**
 * Class MonitorSet
 *
 * Represents a set of sentinel nodes that are monitoring a master with it's slaves
 *
 * @package Sentinel
 */
class MonitorSet
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Client[]
     */
    private $nodes = array();

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

    public function addNode(Client $node)
    {
        $this->nodes[] = $node;
    }

    public function getNodes()
    {
        return \SplFixedArray::fromArray($this->nodes);
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
     * @return Client\SentinelClientAdapter
     * @throws Exception\ConnectionError
     * @throws Exception\ConfigurationError
     */
    public function getMaster()
    {
        if ($this->getNodes()->count() == 0) {
            throw new ConfigurationError('You need to configure and add sentinel nodes before attempting to fetch a master');
        }

        foreach ($this->getNodes() as $sentinelClient) {
            /** @var $sentinelClient Client */
            try {
                $sentinelClient->connect();
                $redisClient = $sentinelClient->getMaster();
                if ($redisClient->isMaster()) {
                    return $redisClient;
                }
            } catch (ConnectionError $e) {
                // on error, try to connect to next node
            }
        }

        throw new ConnectionError('All sentinels are unreachable');
    }
} 