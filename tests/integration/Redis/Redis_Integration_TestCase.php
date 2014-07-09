<?php


namespace Redis;


class Redis_Integration_TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Provides reset functionality:
     * - remove all keys
     *
     * @param Client $redisClient
     */
    protected function resetRedis(Client $redisClient)
    {

    }

    /**
     * This will kill all client connection to the connected node
     *
     * @param Client $redisClient
     */
    protected function killAllClients(Client $redisClient)
    {

    }
} 