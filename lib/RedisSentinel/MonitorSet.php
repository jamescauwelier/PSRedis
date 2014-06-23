<?php

namespace RedisSentinel;

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