<?php

namespace RedisSentinel;

/**
 * Class SentinelNode
 *
 * Represents one single sentinel node and provides identification if we want to connect to it
 *
 * @package RedisSentinel
 */
class SentinelNode {

    /**
     * The name of the sentinel set this node belongs to
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $ipAddress;

    /**
     * @var integer
     */
    private $port;

    public function __construct($name, $ipAddress, $port)
    {
        $this->name = $name;
        $this->ipAddress = $ipAddress;
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }
} 