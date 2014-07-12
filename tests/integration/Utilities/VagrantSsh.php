<?php


namespace Utilities;


class VagrantSsh
{
    private $host;

    public function __construct($host)
    {
        $this->host = $host;
    }


    public function execute($command)
    {
        $fullCommand = sprintf(
            'ssh -i ~/.vagrant.d/insecure_private_key vagrant@%s %s',
            $this->host,
            $command
        );
        exec($fullCommand);

        return $this;
    }
} 