<?php

namespace RedisSentinel;

class SentinelNodeTest extends \PHPUnit_Framework_TestCase
{
    private $ipAddress = '127.0.0.1';
    private $port = 2323;

    public function testSentinelHasIpAddress()
    {
        $sentinel = new SentinelNode($this->ipAddress, $this->port);
        $this->assertEquals($this->ipAddress, $sentinel->getIpAddress(), 'A sentinel location is identified by ip address');
    }

    public function testSentinelRequiresAValidIpAddress()
    {
        $this->setExpectedException('\\RedisSentinel\\Exception\\InvalidProperty', 'A sentinel node requires a valid IP address');
        $sentinel = new SentinelNode('blabla', $this->port);
    }

    public function testSentinelHasPort()
    {
        $sentinel = new SentinelNode($this->ipAddress, $this->port);
        $this->assertEquals($this->port, $sentinel->getPort(), 'A sentinel location needs a port to be identifiable');
    }

    public function testSentinelRefusesTextAsAnInvalidPort()
    {
        $this->setExpectedException('\\RedisSentinel\\Exception\\InvalidProperty', 'A sentinel node requires a valid service port');
        $sentinel = new SentinelNode($this->ipAddress, 'abc');
    }

}
 