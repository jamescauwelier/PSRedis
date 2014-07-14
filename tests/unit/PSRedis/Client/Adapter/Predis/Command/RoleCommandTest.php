<?php

namespace PSRedis\Client\Adapter\Predis\Command;

class RoleCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testThatTheCommandIdIsCorrect()
    {
        $command = new \PSRedis\Client\Adapter\Predis\Command\RoleCommand();
        $this->assertEquals('ROLE', $command->getId(), 'Verifies that the correct id is being used for this command');
    }
}
 