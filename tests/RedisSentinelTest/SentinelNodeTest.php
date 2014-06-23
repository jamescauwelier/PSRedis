<?php

namespace RedisSentinel;

class SentinelNodeTest extends \PHPUnit_Framework_TestCase
{
    private $name = 'redis-set-name';
    private $ipAddress = '127.0.0.1';
    private $port = 2323;

    public function testSentinelHasAName()
    {
        $sentinel = new SentinelNode($this->name, $this->ipAddress, $this->port);
        $this->assertEquals($this->name, $sentinel->getName(), 'A sentinel knows what named redis set it belongs to');
    }

    public function testSentinelHasIpAddress()
    {
        $sentinel = new SentinelNode($this->name, $this->ipAddress, $this->port);
        $this->assertEquals($this->ipAddress, $sentinel->getIpAddress(), 'A sentinel location is identified by ip address');
    }

    public function testSentinelHasPort()
    {
        $sentinel = new SentinelNode($this->name, $this->ipAddress, $this->port);
        $this->assertEquals($this->port, $sentinel->getPort(), 'A sentinel location needs a port to be identifiable');
    }

}
 