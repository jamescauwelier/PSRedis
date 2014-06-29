<?php


namespace Sentinel\Client\Adapter;


class NullSentinelClientAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testThatANullClientAlwaysLooksDisconnected()
    {
        $clientAdapter = new NullSentinelClientAdapter();
        $clientAdapter->connect();

        $this->assertEquals(true, $clientAdapter->isConnected(), 'Connected flag on null adapter is updated after connecting');
    }
}
 