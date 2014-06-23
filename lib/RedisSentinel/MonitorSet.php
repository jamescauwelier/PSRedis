<?php

namespace RedisSentinel;
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
        $validator = Validation::createValidator();
        $violations = $validator->validateValue($name, new NotBlank());
        if ($violations->count() > 0) {
            throw new InvalidProperty('A monitor set needs a valid name');
        }
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
} 