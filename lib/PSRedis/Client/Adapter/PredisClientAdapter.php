<?php

namespace PSRedis\Client\Adapter;

use Predis\Connection\ConnectionException;
use PSRedis\Client\Adapter\Predis\Command\SentinelCommand;
use PSRedis\Client\Adapter\Predis\PredisClientFactory;
use PSRedis\Client\ClientAdapter;
use PSRedis\Client;
use PSRedis\Exception\ConnectionError;
use PSRedis\Exception\SentinelError;

/**
 * Class PredisClientAdapter
 *
 * Adapts the PSRedis\Client objects to Predis
 * @link
 *
 * @package PSRedis\Client\Adapter
 */
class PredisClientAdapter
    extends AbstractClientAdapter
    implements ClientAdapter
{
    /**
     * The Predis client to use when sending commands to the redis server
     * @var \Predis\Client
     */
    private $predisClient;

    /**
     * Factory allows us to mock the creation of the actual redis clients
     * @var \PSRedis\Client\Adapter\Predis\PredisClientFactory
     */
    private $predisClientFactory;

    /**
     * @var string
     */
    private $clientType;

    /**
     * @param PredisClientFactory $clientFactory
     * @param $clientType string
     */
    public function __construct(PredisClientFactory $clientFactory, $clientType)
    {
        $this->predisClientFactory = $clientFactory;
        $this->clientType = $clientType;
    }

    /**
     * @return \Predis\Client
     */
    private function getPredisClient()
    {
        if (empty($this->predisClient)) {
            $this->connect();
        }

        return $this->predisClient;
    }

    /**
     * Creates a connect to Redis or Sentinel using the Predis\Client object.  It proxies the connecting and converts
     * specific client exceptions to more generic adapted ones in PSRedis
     *
     * @throws \PSRedis\Exception\ConnectionError
     */
    public function connect()
    {
        try {
            $this->predisClient = $this->predisClientFactory->createClient($this->clientType, $this->getPredisClientParameters());
            $this->predisClient->connect();
            $this->isConnected = $this->predisClient->isConnected();
        } catch (ConnectionException $e) {
            throw new ConnectionError($e->getMessage());
        }
    }

    private function getPredisClientParameters()
    {
        return array(
            'scheme'    => 'tcp',
            'host'      => $this->ipAddress,
            'port'      => $this->port,
        );
    }

    /**
     * Gets the master node information from a sentinel.  This will still attempt to execute the sentinel command if
     * executed on a redis client, but it will not recognize the command when attempted.
     *
     * @param $nameOfNodeSet
     * @return Client
     * @throws \PSRedis\Exception\SentinelError
     */
    public function getMaster($nameOfNodeSet)
    {
        list($masterIpAddress, $masterPort) = $this->getPredisClient()->sentinel(SentinelCommand::GETMASTER, $nameOfNodeSet);

        if (!empty($masterIpAddress) AND !empty($masterPort)) {
            return new \PSRedis\Client($masterIpAddress, $masterPort, new PredisClientAdapter($this->predisClientFactory, Client::TYPE_REDIS));
        }

        throw new SentinelError('The sentinel does not know the master address');
    }

    /**
     * Inspects the role of the node we are currently connected to
     *
     * @see http://redis.io/commands/role
     * @return mixed
     */
    public function getRole()
    {
        return $this->getPredisClient()->role();
    }

    /**
     * @param $methodName
     * @param array $methodParameters
     * @return mixed|void
     */
    public function __call($methodName, array $methodParameters = array())
    {
        try {
            return call_user_func_array(array($this->getPredisClient(), $methodName), $methodParameters);
        } catch (ConnectionException $e) {
            throw new ConnectionError($e->getMessage());
        }

    }
}