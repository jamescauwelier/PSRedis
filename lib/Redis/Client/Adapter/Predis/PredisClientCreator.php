<?php


namespace Redis\Client\Adapter\Predis;


class PredisClientCreator
    implements PredisClientFactory
{
    public function createSentinelClient(array $parameters = array())
    {
        $predisClient = new \Predis\Client($parameters);
        $predisClient->getProfile()->defineCommand(
            'getmasteraddress', '\\Sentinel\\Client\\Adapter\\Predis\\Command\\GetMasterAddressCommand'
        );
        $predisClient->getProfile()->defineCommand(
            'role', '\\Sentinel\\Client\\Adapter\\Predis\\Command\\RoleCommand'
        );

        return $predisClient;
    }

    public function createRedisClient(array $parameters = array())
    {
        $predisClient = new \Predis\Client($parameters);
        $predisClient->getProfile()->defineCommand(
            'role', '\\Sentinel\\Client\\Adapter\\Predis\\Command\\RoleCommand'
        );

        return $predisClient;
    }
} 