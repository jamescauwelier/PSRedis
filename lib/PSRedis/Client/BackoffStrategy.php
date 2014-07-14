<?php


namespace PSRedis\Client;


interface BackoffStrategy
{
    public function getBackoffInMicroSeconds();
    public function reset();
    public function shouldWeTryAgain();
} 