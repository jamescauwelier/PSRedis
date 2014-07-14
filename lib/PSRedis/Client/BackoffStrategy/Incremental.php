<?php


namespace PSRedis\Client\BackoffStrategy;


use PSRedis\Client\BackoffStrategy;
use PSRedis\Exception\InvalidProperty;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Validation;

class Incremental
    implements BackoffStrategy
{
    private $initialBackoff;

    private $backoffMultiplier;

    private $nextBackoff;

    private $maxAttempts = false;

    private $attempts = 0;

    public function __construct($initialBackoff, $backoffMultiplier)
    {
        $this->guardThatBackoffIsNotNegative($initialBackoff);

        $this->initialBackoff = $initialBackoff;
        $this->backoffMultiplier = $backoffMultiplier;
        $this->reset();
    }

    public function reset()
    {
        $this->nextBackoff = $this->initialBackoff;
        $this->attempts = 0;
    }

    public function setMaxAttempts($maxAttempts)
    {
        $this->maxAttempts = $maxAttempts;
    }

    public function getBackoffInMicroSeconds()
    {
        $currentBackoff = $this->nextBackoff;
        $this->nextBackoff *= $this->backoffMultiplier;
        $this->attempts += 1;
        return $currentBackoff;
    }

    /**
     * @param $initialBackoff
     * @throws \Redis\Exception\InvalidProperty
     */
    private function guardThatBackoffIsNotNegative($initialBackoff)
    {
        $validator = Validation::createValidator();
        $violations = $validator->validateValue($initialBackoff, new Range(array('min' => 0)));
        if ($violations->count() > 0) {
            throw new InvalidProperty('The initial backoff cannot be smaller than zero');
        }
    }

    public function shouldWeTryAgain()
    {
        return ($this->maxAttempts === false) ? true : $this->maxAttempts >= $this->attempts + 1;
    }
} 