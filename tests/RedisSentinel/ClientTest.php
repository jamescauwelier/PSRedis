<?php


namespace RedisSentinel;


class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \RedisSentinel\SentinelNode
     */
    private function createMockedSentinelNode()
    {
        $sentinelNode = \Phake::mock('RedisSentinel\SentinelNode');

        return $sentinelNode;
    }

    public function testCreationOfASentinelClient()
    {
        $node = $this->createMockedSentinelNode();
        $client = new Client($node);
        $this->assertAttributeEquals($node, 'node', $client, 'A sentinel client can be created');
    }

    public function testCreationOfASentinelClientFailsWithoutNodeObject()
    {
        $this->setExpectedException('\\PHPUnit_Framework_Error', 'Argument 1 passed to RedisSentinel\Client::__construct() must be an instance of RedisSentinel\SentinelNode, instance of stdClass given');
        new Client(new \StdClass());
    }
}
 