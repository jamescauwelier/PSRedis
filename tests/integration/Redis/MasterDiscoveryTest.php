<?php


namespace Redis;


class MasterDiscoveryTest extends \PHPUnit_Framework_TestCase
{
    public function testDiscoveryFailsWithoutAMaster()
    {
        $this->markTestSkipped('todo');
    }

    public function testDiscoveryOfMasterSucceedsWithAMaster()
    {
        $this->markTestSkipped('todo');
    }

    public function testDiscoveryOfMasterSucceedsWithTheFirstSentinelOffline()
    {
        $this->markTestSkipped('todo');
    }

    public function testDiscoveryWithBackoffWorksWithSentinelsTemporarilyUnreachable()
    {
        $this->markTestSkipped('todo');
    }

    public function testDiscoveryWithBackoffFailsWhenSentinelsStayOffline()
    {
        $this->markTestSkipped('todo');
    }
}
 