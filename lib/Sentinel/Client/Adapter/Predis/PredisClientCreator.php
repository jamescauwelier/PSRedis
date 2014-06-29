<?php


namespace Sentinel\Client\Adapter\Predis;


class PredisClientCreator
    implements PredisClientFactory
{
    public function create(array $parameters = array())
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
} 