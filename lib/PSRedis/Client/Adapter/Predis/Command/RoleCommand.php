<?php


namespace PSRedis\Client\Adapter\Predis\Command;


use Predis\Command\Command;

/**
 * Class RoleCommand
 *
 * Custom Predis command to find out the role of the node connected to (slave, master, sentinel) with some contextual
 * information added.
 *
 * @package PSRedis\Client\Adapter\Predis\Command
 * @link http://redis.io/commands/role
 */
class RoleCommand
    extends Command
{
    public function getId()
    {
        return 'ROLE';
    }
} 