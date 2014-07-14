<?php


namespace PSRedis\Client\Adapter\Predis\Command;


use Predis\Command\AbstractCommand;

class SentinelCommand
    extends AbstractCommand
{
    const GETMASTER = 'get-master-addr-by-name';
    const GETSLAVES = 'slaves';

    public function getId()
    {
        return 'SENTINEL';
    }
} 