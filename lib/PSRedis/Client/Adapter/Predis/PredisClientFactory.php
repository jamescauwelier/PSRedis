<?php

namespace PSRedis\Client\Adapter\Predis;

/**
 * Interface PredisClientFactory
 *
 * Creates the actual clients to talk to Redis and Sentinel nodes with.  This allows us to mock the objects created
 * in order to unit test the library
 *
 * @package PSRedis\Client\Adapter\Predis
 */
interface PredisClientFactory
{
    public function createClient($clientType, array $parameters = array());
} 