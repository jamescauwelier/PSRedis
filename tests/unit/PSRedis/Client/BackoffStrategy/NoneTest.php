<?php


namespace PSRedis\MasterDiscovery\BackoffStrategy;


class NoneTest extends \PHPUnit_Framework_TestCase
{
    public function testBackoffIsZero()
    {
        $backoff = new None();
        $this->assertEquals(0, $backoff->getBackoffInMicroSeconds(), 'Backoff should be zero');
    }

    public function testBackoffIsZeroAfterReset()
    {
        $backoff = new None();
        $backoff->reset();
        $this->assertEquals(0, $backoff->getBackoffInMicroSeconds(), 'Backoff is still zero after reset');
    }

    public function testTryingAgain()
    {
        $backoff = new None();
        $this->assertFalse($backoff->shouldWeTryAgain(), 'Never try again with this strategy');
    }
}
 