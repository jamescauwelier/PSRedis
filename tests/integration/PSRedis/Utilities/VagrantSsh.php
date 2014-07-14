<?php


namespace PSRedis\Utilities;


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
            'ssh -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no -T -i ~/.vagrant.d/insecure_private_key vagrant@%s %s',
            $this->host,
            $command
        );
        ob_start();
        exec($fullCommand);
        ob_flush();

        return $this;
    }
} 