<?php


namespace PSRedis\Client\Adapter\Predis\Command;


use Predis\Command\Command;

/**
 * Class SentinelCommand
 *
 * Custom Predis command to use the SENTINEL command
 *
 * @package PSRedis\Client\Adapter\Predis\Command
 * @see http://redis.io/topics/sentinel Contains more information on the sentinel API.
 */
class SentinelCommand
    extends Command
{
    const GETMASTER = 'get-master-addr-by-name';
    const GETSLAVES = 'slaves';

    public function getId()
    {
        return 'SENTINEL';
    }
} 