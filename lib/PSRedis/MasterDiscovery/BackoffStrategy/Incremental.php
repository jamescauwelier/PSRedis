<?php


namespace PSRedis\MasterDiscovery\BackoffStrategy;


use PSRedis\MasterDiscovery\BackoffStrategy;
use PSRedis\Exception\InvalidProperty;

/**
 * Class Incremental
 *
 * Implements incremental backoff logic.  By changing the initial backoff and multiplier, the backoff can be choosen in
 * a very flexible way.  Bad configuration could lead to infinite loops though, so be carefull on what kind of logic you
 * implement
 *
 * @package PSRedis\Client\BackoffStrategy
 */
class Incremental
    implements BackoffStrategy
{
    /**
     * The initial backoff in microseconds
     * @var int
     */
    private $initialBackoff;

    /**
     * The number to multiply the previous backoff with on each backoff.
     * @var float
     */
    private $backoffMultiplier;

    /**
     * Holds the next backoff value
     * @var float
     */
    private $nextBackoff;

    /**
     * The maximum number of attempts to take before we don't backoff anymore
     * @var bool|int
     */
    private $maxAttempts = false;

    /**
     * The number of attempts that were already made
     * @var int
     */
    private $attempts = 0;

    public function __construct($initialBackoff, $backoffMultiplier)
    {
        $this->guardThatBackoffIsNotNegative($initialBackoff);

        $this->initialBackoff = $initialBackoff;
        $this->backoffMultiplier = $backoffMultiplier;
        $this->reset();
    }

    /**
     * Resets the state of the backoff implementation.  Should be used when engaging in a logically different master
     * discovery or reconnection attempt
     */
    public function reset()
    {
        $this->nextBackoff = $this->initialBackoff;
        $this->attempts = 0;
    }

    /**
     * @param $maxAttempts int
     */
    public function setMaxAttempts($maxAttempts)
    {
        $this->maxAttempts = $maxAttempts;
    }

    /**
     * @return int
     */
    public function getBackoffInMicroSeconds()
    {
        $currentBackoff = $this->nextBackoff;
        $this->nextBackoff *= $this->backoffMultiplier;
        $this->attempts += 1;
        return $currentBackoff;
    }

    /**
     * Validator for the initial backoff
     * @param $initialBackoff
     * @throws \PSRedis\Exception\InvalidProperty
     */
    private function guardThatBackoffIsNotNegative($initialBackoff)
    {
        if ($initialBackoff < 0) {
            throw new InvalidProperty('The initial backoff cannot be smaller than zero');
        }
    }

    /**
     * Verifies if we should stop trying to discover the master or backoff and try again
     * @return bool
     */
    public function shouldWeTryAgain()
    {
        return ($this->maxAttempts === false) ? true : $this->maxAttempts >= $this->attempts + 1;
    }
} 