<?php

namespace PSRedis\Client\Adapter\Predis;


interface PredisClientFactory
{
    public function createClient($clientType, array $parameters = array());
} 