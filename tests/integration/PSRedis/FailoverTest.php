<?php


namespace PSRedis;


use PSRedis\Client\Adapter\Predis\PredisClientCreator;
use PSRedis\Client\Adapter\PredisClientAdapter;
use PSRedis\MasterDiscovery\BackoffStrategy\Incremental;

require_once __DIR__ . '/Redis_Integration_TestCase.php';

class FailoverTest extends Redis_Integration_TestCase
{
    public function testThatFailoverIsInitiatedAndFailingCommandsAreRetried()
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

        // configure how to back-off on reconnection attempts
        $backoffStrategy = new Incremental(500000, 2);
        $backoffStrategy->setMaxAttempts(10);

        // now we can start configuring the sentinel in the master discovery object

        $masterDiscovery = new MasterDiscovery('integrationtests');
        $masterDiscovery->setBackoffStrategy($backoffStrategy);
        $masterDiscovery->addSentinel($sentinel1);
        $masterDiscovery->addSentinel($sentinel2);
        $masterDiscovery->addSentinel($sentinel3);

        // configure the HAClient that we'll use to talk to the redis master as a proxy

        $HAClient = new HAClient($masterDiscovery);

        // simulate a segfault in 5s
        $this->debugSegfaultToMaster();

        for ($i = 1; $i <= 30; $i++) {
            $HAClient->incr(__METHOD__);
        }

        $this->assertEquals(30, $HAClient->get(__METHOD__), 'Test that all increment calls were executed');
    }
} 