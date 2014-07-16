<?php


namespace PSRedis\MasterDiscovery\BackoffStrategy;


use PSRedis\MasterDiscovery\BackoffStrategy;

/**
 * Class None
 *
 * Makes use of the Incremental backoff strategy with pre-defined settings.  It allows you to make the backoff
 * strategy more readable and explicit in your code instead of expressing yourself with integer and float
 * configuration parameters
 *
 * @package PSRedis\MasterDiscovery\BackoffStrategy
 */
class None
    implements BackoffStrategy
{
    private $incrementalStrategy;

    public function __construct()
    {
        $this->incrementalStrategy = new Incremental(0, 0);
        $this->incrementalStrategy->setMaxAttempts(0);
    }

    /**
     *
     */
    public function reset()
    {
        $this->incrementalStrategy->reset();
    }

    public function getBackoffInMicroSeconds()
    {
        return $this->incrementalStrategy->getBackoffInMicroSeconds();
    }

    public function shouldWeTryAgain()
    {
        return $this->incrementalStrategy->shouldWeTryAgain();
    }
} 