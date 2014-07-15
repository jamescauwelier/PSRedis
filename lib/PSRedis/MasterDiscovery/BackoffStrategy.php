<?php


namespace PSRedis\MasterDiscovery;

/**
 * Interface BackoffStrategy
 *
 * Implements logic to decide on whether we should try again after a backoff and how long to backoff
 *
 * @package PSRedis\MasterDiscovery
 */
interface BackoffStrategy
{
    /**
     * @return int
     */
    public function getBackoffInMicroSeconds();

    /**
     * Resets the state of the backoff implementation.  Should be used when engaging in a logically different master
     * discovery or reconnection attempt
     */
    public function reset();

    /**
     * Verifies if we should stop trying to discover the master or backoff and try again
     * @return bool
     */
    public function shouldWeTryAgain();
} 