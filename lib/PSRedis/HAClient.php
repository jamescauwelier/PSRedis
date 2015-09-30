<?php


namespace PSRedis;

use PSRedis\Exception\ConnectionError;
use PSRedis\Exception\RoleError;
use PSRedis\Exception\SentinelError;
use PSRedis\MasterDiscovery\BackoffStrategy;
use PSRedis\MasterDiscovery\BackoffStrategy\None;
use PSRedis\NodeDiscovery\MasterDiscovery;
use PSRedis\Sentinel\Configuration;

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
     * Configures what sentinels to talk to to discover nodes
     * @var Configuration
     */
    private $sentinelConfiguration;

    /**
     * Holds all configuration to the sentinels to execute the master discovery process
     * @var MasterDiscovery
     */
    private $masterDiscovery;

    /**
     * @var BackoffStrategy
     */
    private $backoffStrategy;

    /**
     * The callable to be called when backing off during master discovery.  To be used for logging and making the
     * code testable (See integration tests)
     * @var callable
     */
    private $backoffObserver;

    /**
     * The master node to connect to
     * @var Client
     */
    private $master;

    /**
     * @param Configuration $sentinelConfiguration
     * @param MasterDiscovery $masterDiscovery
     */
    public function __construct(
        Configuration $sentinelConfiguration,
        MasterDiscovery $masterDiscovery
    )
    {
        $this->sentinelConfiguration = $sentinelConfiguration;
        $this->masterDiscovery = $masterDiscovery;
        $this->setBackoffStrategy(new None());
    }

    /**
     * @param BackoffStrategy $backoffStrategy
     */
    public function setBackoffStrategy(BackoffStrategy $backoffStrategy)
    {
        $this->backoffStrategy = $backoffStrategy;
    }


    /**
     * @param callable $observer
     */
    public function setBackoffObserver (callable $observer)
    {
        $this->backoffObserver = $observer;
    }

    /**
     * Investigates whether we have already discovered where the master currently is to be found
     *
     * @return bool
     */
    private function masterIsUnknown()
    {
        return empty($this->master);
    }

    /**
     * Removes the current master after connection errors so that we are forced to start the discovery process again
     * on the next command proxy
     *
     * @return void
     */
    private function invalidateMasterConnection()
    {
        $this->master = null;
    }

    /**
     * We assume that calls to non-existing methods have a corresponding method in the redis client that is being used.
     * We therefore proxy the request to the current master and if it fails because of connection errors, we attempt
     * to rediscover the master so that we can re-try the command on that server.
     *
     * @param $methodName
     * @param array $methodArguments
     */
    public function __call($methodName, array $methodArguments = array())
    {
        try {

            return $this->proxyFunctionCallToMaster($methodName, $methodArguments);

        } catch (ConnectionError $e) {

            // retry proxying the function only once.  When backoff is needed, it should be implemented in the MasterDiscovery object
            $this->invalidateMasterConnection();
            return $this->proxyFunctionCallToMaster($methodName, $methodArguments);

        }
    }

    /**
     * Proxies a call to a non-existing method in this object to the redis client
     *
     * @param $name
     * @param array $arguments
     * @return mixed
     */
    private function proxyFunctionCallToMaster($name, array $arguments)
    {
        if ($this->masterIsUnknown()) {

            $this->backoffStrategy->reset();

            do {

                try {
                    $this->master = $this->masterDiscovery->getNode($this->sentinelConfiguration);
                    break;
                } catch (ConnectionError $e) {
                    // on connection errors, back-off
                } catch (SentinelError $e) {
                    // on sentinel errors, back-off
                } catch (RoleError $e) {
                    // when master has stepped down, back-off and try again
                }


                if ($this->backoffStrategy->shouldWeTryAgain()) {
                    $backoffInMicroseconds = $this->backoffStrategy->getBackoffInMicroSeconds();
                    if (!empty($this->backoffObserver)) {
                        call_user_func($this->backoffObserver, $backoffInMicroseconds);
                    }
                    usleep($backoffInMicroseconds);
                }

            } while ($this->backoffStrategy->shouldWeTryAgain());


        }

        return call_user_func_array(array($this->master, $name), $arguments);
    }
} 