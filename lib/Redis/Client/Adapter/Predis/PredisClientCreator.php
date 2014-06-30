<?php


namespace Redis\Client\Adapter\Predis;


use Redis\Client;
use Redis\Exception\ConfigurationError;

class PredisClientCreator
    implements PredisClientFactory
{
    public function createClient($clientType, array $parameters = array())
    {
        switch($clientType)
        {
            case Client::TYPE_REDIS:
                return $this->createRedisClient($parameters);
            case Client::TYPE_SENTINEL:
                return $this->createSentinelClient($parameters);
        }

        throw new ConfigurationError('To create a client, you need to provide a valid client type');
    }

    private function createSentinelClient(array $parameters = array())
    {
        $predisClient = new \Predis\Client($parameters);
        $predisClient->getProfile()->defineCommand(
            'sentinel', '\\Redis\\Client\\Adapter\\Predis\\Command\\SentinelCommand'
        );
        $predisClient->getProfile()->defineCommand(
            'role', '\\Redis\\Client\\Adapter\\Predis\\Command\\RoleCommand'
        );

        return $predisClient;
    }

    private function createRedisClient(array $parameters = array())
    {
        $predisClient = new \Predis\Client($parameters);
        $predisClient->getProfile()->defineCommand(
            'role', '\\Redis\\Client\\Adapter\\Predis\\Command\\RoleCommand'
        );

        return $predisClient;
    }
} 