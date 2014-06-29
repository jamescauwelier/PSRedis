<?php


namespace Sentinel\Client\Adapter;


class PredisSentinelClientAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testThatAPredisClientIsCreatedOnConnect()
    {
        $clientAdapter = new PredisSentinelClientAdapter();
        $clientAdapter->setIpAddress('127.0.0.1');
        $clientAdapter->setPort(4545);
        $clientAdapter->connect();

        $this->assertAttributeInstanceOf('\\Predis\\Client', 'predisClient', $clientAdapter, 'The adapter should create and configure a \\Predis\\Client object');
    }
}
 