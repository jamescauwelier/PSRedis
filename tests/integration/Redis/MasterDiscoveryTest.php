<?php


namespace Redis;


use Redis\Client\Adapter\Predis\PredisClientCreator;
use Redis\Client\Adapter\PredisClientAdapter;
use Redis\Client\BackoffStrategy\Incremental;

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
        $this->assertInstanceOf('\\Redis\\Client', $master, 'Master is returned after successful master discovery');
        $this->assertAttributeEquals('192.168.50.40', 'ipAddress', $master, 'The master ip returned is correct');
        $this->assertAttributeEquals('6379', 'port', $master, 'The master ip returned is correct');
    }

    public function testDiscoveryWithoutBackoffFailsWithSentinelsUnreachable()
    {
        $this->setExpectedException('\\Redis\\Exception\\ConnectionError', 'All sentinels are unreachable');

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

        // we need to fork off a child process in order to bring the sentinels back online while discovering the master
        $processId = \pcntl_fork();
        if ($processId == -1) {

            throw new \Exception('could not fork to re-enable the sentinels');

        } else if ($processId) {

            pcntl_wait($childProcessStatus); //Protect against Zombie children

            // try to discover the master
            $master = $masterDiscovery->getMaster();

            // after master discovery, at least one sentinel is connected

            $this->assertTrue(
                (bool) ($sentinel1->isConnected() | $sentinel2->isConnected() | $sentinel3->isConnected()),
                'At least one of the sentinels is back online'
            );

            // master discovery returned a client to the correct node

            $this->assertInstanceOf('\\Redis\\Client', $master, 'Master is returned after successful master discovery');
            $this->assertAttributeEquals('192.168.50.40', 'ipAddress', $master, 'The master ip returned is correct');
            $this->assertAttributeEquals('6379', 'port', $master, 'The master ip returned is correct');

        } else {

            // forked off in a child process to re-enable the sentinels while discovering the master

            $this->enableSentinelAt('192.168.50.40');
            $this->enableSentinelAt('192.168.50.41');
            $this->enableSentinelAt('192.168.50.30');

            // making sure that we exit the child process!

            exit();
        }

    }

    public function testDiscoveryWithBackoffFailsWhenSentinelsStayOffline()
    {
        $this->setExpectedException('\\Redis\\Exception\\ConnectionError', 'All sentinels are unreachable');

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
 