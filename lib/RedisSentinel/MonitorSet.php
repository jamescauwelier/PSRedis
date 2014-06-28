<?php

namespace RedisSentinel;

use RedisSentinel\Exception\ConfigurationError;
use RedisSentinel\Exception\ConnectionError;
use RedisSentinel\Exception\InvalidProperty;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;

/**
 * Class MonitorSet
 *
 * Represents a set of sentinel nodes that are monitoring a master with it's slaves
 *
 * @package RedisSentinel
 */
class MonitorSet
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var SentinelNode[]
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

    public function addNode(SentinelNode $node)
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

    public function getMaster()
    {
        if ($this->getNodes()->count() == 0) {
            throw new ConfigurationError('You need to configure and add sentinel nodes before attempting to fetch a master');
        }

        foreach ($this->getNodes() as $sentinelNode) {
            /** @var $sentinelNode SentinelNode */
            try {
                $sentinelNode->connect();
                return;
            } catch (ConnectionError $e) {
                // on error, try to connect to next node
            }
        }

        throw new ConnectionError('All sentinels are unreachable');
    }
} 