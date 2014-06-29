<?php


namespace Sentinel\Client\Adapter\Predis\Command;


use Predis\Command\AbstractCommand;

class GetMasterAddressCommand
    extends AbstractCommand
{
    public function getId()
    {
        return 'SENTINEL get-master-addr-by-name';
    }
} 