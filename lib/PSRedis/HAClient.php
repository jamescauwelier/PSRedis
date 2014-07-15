<?php


namespace PSRedis;
use Predis\Connection\ConnectionException;

/**
 * Class HAClient
 *
 * High Availability Client will proxy all method calls to the actual client implementation connecting to your master
 * node after initiating the discovery process.  Upon connection failures, this client will rediscover the master
 * node and retry the failed command
 *
 * @package PSRedis
 */
class HAClient 
{
    /**
     * Holds all configuration to the sentinels to execute the master discovery process
     * @var MasterDiscovery
     */
    private $masterDiscovery;

    /**
     * The master node to connect to
     * @var Client
     */
    private $master;

    /**
     * @param MasterDiscovery $masterDiscovery
     */
    public function __construct(MasterDiscovery $masterDiscovery)
    {
        $this->masterDiscovery = $masterDiscovery;
    }

    private function masterIsKnown()
    {
        return !empty($this->master);
    }

    /**
     * Proxies calls to the master client
     *
     * @param $name
     * @param array $arguments
     */
    public function __call($name, array $arguments = array())
    {
        if (!$this->masterIsKnown()) {
            $this->master = $this->masterDiscovery->getMaster();
        }

        return call_user_func_array(array($this->master, $name), $arguments);
    }


    /**public function __call($name, array $arguments = array())
    {
        if (!$this->masterIsKnown()) {
            $this->master = $this->masterDiscovery->getMaster();
            $this->backoff->reset();
        }

        try {
            $this->proxyCall($name, $arguments);
        } catch (ConnectionException $e) {
            // rediscover the master after a back-off

            $this->master = null;
            if ($backoff->shouldWeTryAgain()) {
                sleep($backoff->getBackoffInMicroseconds());
                $this->_call($name, $arguments);
            } else {
                throw $e;
            }

        }
    }

    /**
     * Fails over to another node upon connection exceptions by finding the new master
     */
    /**private function failover()
    {
        $this->master = null;
    }**/
} 