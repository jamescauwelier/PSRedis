<?php

namespace PSRedis\Client\Adapter\Predis\Command;


class SentinelCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testThatTheCorrectIdIsReturned()
    {
        $command = new \PSRedis\Client\Adapter\Predis\Command\SentinelCommand();
        $this->assertEquals('SENTINEL', $command->getId(), 'Test that the id of the command is correct (SENTINEL)');
    }
}
 