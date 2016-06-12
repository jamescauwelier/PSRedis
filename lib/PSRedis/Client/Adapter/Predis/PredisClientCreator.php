<?php


namespace PSRedis\Client\Adapter\Predis;


use PSRedis\Client;
use PSRedis\Exception\ConfigurationError;

/**
 * Class PredisClientCreator
 *
 * Factory to create the Predis clients that allow us to talk to Redis and Sentinel nodes.
 *
 * @package PSRedis\Client\Adapter\Predis
 */
class PredisClientCreator
    implements PredisClientFactory
{
    /**
     * @var array
     */
    private $options;

    /**
     * @param array|null $options Additional options for the Predis Client
     */
    public function __construct(array $options = null)
    {
        $this->options = $options;
    }

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
        $predisClient = new \Predis\Client($parameters, $this->options);
        $predisClient->getCommandFactory()->defineCommand(
            'sentinel', '\\PSRedis\\Client\\Adapter\\Predis\\Command\\SentinelCommand'
        );
        $predisClient->getCommandFactory()->defineCommand(
            'role', '\\PSRedis\\Client\\Adapter\\Predis\\Command\\RoleCommand'
        );

        return $predisClient;
    }

    private function createRedisClient(array $parameters = array())
    {
        $predisClient = new \Predis\Client($parameters, $this->options);
        $predisClient->getCommandFactory()->defineCommand(
            'role', '\\PSRedis\\Client\\Adapter\\Predis\\Command\\RoleCommand'
        );

        return $predisClient;
    }
} 
