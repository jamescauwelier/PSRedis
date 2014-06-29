<?php
/**
 * Created by PhpStorm.
 * User: jamescauwelier
 * Date: 6/28/14
 * Time: 6:17 PM
 */

namespace Redis\Client\Adapter\Predis;


interface PredisClientFactory
{
    /**
     * @param array $parameters
     * @return \Redis\Client
     */
    public function createSentinelClient(array $parameters = array());

    /**
     * @param array $parameters
     * @return \Redis\Client
     */
    public function createRedisClient(array $parameters = array());
} 