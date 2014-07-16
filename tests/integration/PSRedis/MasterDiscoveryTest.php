<?php


namespace PSRedis;


use PSRedis\Client\Adapter\Predis\PredisClientCreator;
use PSRedis\Client\Adapter\PredisClientAdapter;
use PSRedis\MasterDiscovery\BackoffStrategy\Incremental;

require_once __DIR__ . '/Redis_Integration_TestCase.php';

class MasterDiscoveryTest extends Redis_Integration_TestCase
{
    public function setUp()
    {
        $this->initializeReplicationSet();
    }

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

        $this->assertInstanceOf('\\PSRedis\\Client', $master, 'Master is returned after successful master discovery');
        $this->assertAttributeEquals('192.168.50.40', 'ipAddress', $master, 'The master ip returned is correct');
        $this->assertAttributeEquals('6379', 'port', $master, 'The master ip returned is correct');
    }

    public function testDiscoveryOfMasterSucceedsWithTheFirstSentinelOffline()
    {
        $this->disableSentinelAt('192.168.50.40');

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

        $this->assertFalse($sentinel1->isConnected(), 'The first sentinel is offline');
        $this->assertInstanceOf('\\PSRedis\\Client', $master, 'Master is returned after successful master discovery');
        $this->assertAttributeEquals('192.168.50.40', 'ipAddress', $master, 'The master ip returned is correct');
        $this->assertAttributeEquals('6379', 'port', $master, 'The master ip returned is correct');
    }

    public function testDiscoveryWithoutBackoffFailsWithSentinelsUnreachable()
    {
        $this->setExpectedException('\\PSRedis\\Exception\\ConnectionError', 'All sentinels are unreachable');

        // disable sentinel on all nodes
        $this->disableSentinelAt('192.168.50.40');
        $this->disableSentinelAt('192.168.50.41');
        $this->disableSentinelAt('192.168.50.30');

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

        // try to discover the master
        $master = $masterDiscovery->getMaster();
    }

    public function testDiscoveryWithBackoffWorksWithSentinelsTemporarilyUnreachable()
    {
        // disable sentinel on all nodes
        $this->disableSentinelAt('192.168.50.40');
        $this->disableSentinelAt('192.168.50.41');
        $this->disableSentinelAt('192.168.50.30');

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

        // configure a backoff strategy
        $incrementalBackoff = new Incremental(500, 1.5);
        $incrementalBackoff->setMaxAttempts(10);
        $masterDiscovery->setBackoffStrategy($incrementalBackoff);
        $masterDiscovery->setBackoffObserver(array($this, 'enableAllSentinels'));

        // try to discover the master
        $master = $masterDiscovery->getMaster();

        // after master discovery, at least one sentinel is connected

        $this->assertTrue(
            (bool) ($sentinel1->isConnected() | $sentinel2->isConnected() | $sentinel3->isConnected()),
            'At least one of the sentinels is back online'
        );

        // master discovery returned a client to the correct node

        $this->assertInstanceOf('\\PSRedis\\Client', $master, 'Master is returned after successful master discovery');
        $this->assertAttributeEquals('192.168.50.40', 'ipAddress', $master, 'The master ip returned is correct');
        $this->assertAttributeEquals('6379', 'port', $master, 'The master ip returned is correct');

    }

    public function enableAllSentinels()
    {
        $this->enableSentinelAt('192.168.50.40');
        $this->enableSentinelAt('192.168.50.41');
        $this->enableSentinelAt('192.168.50.30');
    }

    public function testDiscoveryWithBackoffFailsWhenSentinelsStayOffline()
    {
        $this->setExpectedException('\\PSRedis\\Exception\\ConnectionError', 'All sentinels are unreachable');

        // disable sentinel on all nodes
        $this->disableSentinelAt('192.168.50.40');
        $this->disableSentinelAt('192.168.50.41');
        $this->disableSentinelAt('192.168.50.30');

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

        // configure a backoff strategy
        $incrementalBackoff = new Incremental(500, 1.5);
        $incrementalBackoff->setMaxAttempts(5);
        $masterDiscovery->setBackoffStrategy($incrementalBackoff);

        // try to discover the master
        $master = $masterDiscovery->getMaster();
    }
}
 