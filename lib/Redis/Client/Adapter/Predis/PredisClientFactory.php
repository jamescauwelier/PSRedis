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
    public function createClient($clientType, array $parameters = array());
} 