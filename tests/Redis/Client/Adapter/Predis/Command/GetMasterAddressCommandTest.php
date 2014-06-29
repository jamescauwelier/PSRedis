<?php

namespace Redis\Client\Adapter\Predis\Command;


class GetMasterAddressCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testThatTheCorrectIdIsReturned()
    {
        $command = new \Redis\Client\Adapter\Predis\Command\GetMasterAddressCommand();
        $this->assertEquals('SENTINEL get-master-addr-by-name', $command->getId(), 'Test that the id of the command is correct (SENTINEL get-master-addr-by-name)');
    }
}
 