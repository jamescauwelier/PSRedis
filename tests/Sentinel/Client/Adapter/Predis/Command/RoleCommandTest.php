<?php


class RoleCommandTest extends PHPUnit_Framework_TestCase
{
    public function testThatTheCommandIdIsCorrect()
    {
        $command = new \Sentinel\Client\Adapter\Predis\Command\RoleCommand();
        $this->assertEquals('ROLE', $command->getId(), 'Verifies that the correct id is being used for this command');
    }
}
 