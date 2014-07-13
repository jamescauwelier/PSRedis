<?php


namespace Redis;


use Redis\Client\Adapter\Predis\PredisClientCreator;
use Redis\Client\Adapter\PredisClientAdapter;

require_once __DIR__ . '/Redis_Integration_TestCase.php';

class MasterDiscoveryTest extends Redis_Integration_TestCase
{
    public function testDiscoveryOfMasterSucceedsWithAMaster()
    {
        // we need a factory to create the clients
        $clientFactory = new PredisClientCreator();

        // we need an adapter for each sentinel client too!

        $clientAdapter = new PredisClientAdapter($clientFactory, Client::TYPE_SENTINEL);
        $sentinel1 = new Client('192.168.50.40', '26379', $clientAdapter);

        $clientAdapter = new PredisClientAdapter($clientFactory, Client::TYPE_SENTINEL);
        $sentinel2 = new Client('192.168.50.41', '26379', $clientAdapter);

        $clientAdapter = new PredisClientAdapter($clientFactory, Client::TYPE_SENTINEL);
        $sentinel3 = new Client('192.168.50.30', '26379', $clientAdapter);

        // now we can start discovering where the master is

        $masterDiscovery = new MasterDiscovery('integrationtests');
        $masterDiscovery->addSentinel($sentinel1);
        $masterDiscovery->addSentinel($sentinel2);
        $masterDiscovery->addSentinel($sentinel3);

        $master = $masterDiscovery->getMaster();

        $this->assertInstanceOf('\\Redis\\Client', $master, 'Master is returned after successful master discovery');
        $this->assertAttributeEquals('192.168.50.40', 'ipAddress', $master, 'The master ip returned is correct');
        $this->assertAttributeEquals('6379', 'port', $master, 'The master ip returned is correct');
    }

    public function testDiscoveryOfMasterSucceedsWithTheFirstSentinelOffline()
    {
        $this->putFirstSentinelOffline();

        // we need a factory to create the clients
        $clientFactory = new PredisClientCreator();

        // we need an adapter for each sentinel client too!

        $clientAdapter = new PredisClientAdapter($clientFactory, Client::TYPE_SENTINEL);
        $sentinel1 = new Client('192.168.50.40', '26379', $clientAdapter);

        $clientAdapter = new PredisClientAdapter($clientFactory, Client::TYPE_SENTINEL);
        $sentinel2 = new Client('192.168.50.41', '26379', $clientAdapter);

        $clientAdapter = new PredisClientAdapter($clientFactory, Client::TYPE_SENTINEL);
        $sentinel3 = new Client('192.168.50.30', '26379', $clientAdapter);

        // now we can start discovering where the master is

        $masterDiscovery = new MasterDiscovery('integrationtests');
        $masterDiscovery->addSentinel($sentinel1);
        $masterDiscovery->addSentinel($sentinel2);
        $masterDiscovery->addSentinel($sentinel3);

        $master = $masterDiscovery->getMaster();

        $this->assertInstanceOf('\\Redis\\Client', $master, 'Master is returned after successful master discovery');
        $this->assertAttributeEquals('192.168.50.40', 'ipAddress', $master, 'The master ip returned is correct');
        $this->assertAttributeEquals('6379', 'port', $master, 'The master ip returned is correct');
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
 