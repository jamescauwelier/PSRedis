<?php


namespace PSRedis;

use PSRedis\Utilities\VagrantSsh;

require_once __DIR__ . '/Utilities/VagrantSsh.php';

class Redis_Integration_TestCase extends \PHPUnit_Framework_TestCase
{
    private $masterSshConnection;

    private $slaveSshConnection;

    private $sentinelSshConnection;

    public function setUp()
    {
        $this->initializeReplicationSet();
    }

    /**
     * Initializes a replication set by using vagrant to spin up and ansible to provision a set of redis servers
     */
    protected function initializeReplicationSet()
    {
        // reset configuration for master node
        $this->getMasterSshConnection()
            ->execute('sudo stop redis')
            ->execute('sudo stop sentinel')
            ->execute('sudo -u redis cp /etc/redis/redis.example.conf /etc/redis/redis.conf')
            ->execute('sudo -u redis cp /etc/redis/sentinel.example.conf /etc/redis/sentinel.conf')
        ;

        // reset configuration for slave node
        $this->getSlaveSshConnection()
            ->execute('sudo stop redis')
            ->execute('sudo stop sentinel')
            ->execute('sudo -u redis cp /etc/redis/redis.example.conf /etc/redis/redis.conf')
            ->execute('sudo -u redis cp /etc/redis/sentinel.example.conf /etc/redis/sentinel.conf')
        ;

        // reset configuration for extra sentinel node
        $this->getSentinelSshConnection()
            ->execute('sudo stop sentinel')
            ->execute('sudo -u redis cp /etc/redis/sentinel.example.conf /etc/redis/sentinel.conf')
        ;

        // restart the master node and empty database
        $this->getMasterSshConnection()
            ->execute('sudo start redis')
            ->execute('redis-cli FLUSHALL')
        ;

        // restart slave node
        $this->getSlaveSshConnection()
            ->execute('sudo start redis')
        ;

        // start sentinels
        $this->getMasterSshConnection()->execute('sudo start sentinel');
        $this->getSlaveSshConnection()->execute('sudo start sentinel');
        $this->getSentinelSshConnection()->execute('sudo start sentinel');

    }

    private function getMasterSshConnection()
    {
        if (empty($this->masterSshConnection)) {
            $this->masterSshConnection = new VagrantSsh('192.168.50.40');
        }

        return $this->masterSshConnection;
    }

    private function getSlaveSshConnection()
    {
        if (empty($this->slaveSshConnection)) {
            $this->slaveSshConnection = new VagrantSsh('192.168.50.41');
        }

        return $this->slaveSshConnection;
    }

    private function getSentinelSshConnection()
    {
        if (empty($this->sentinelSshConnection)) {
            $this->sentinelSshConnection = new VagrantSsh('192.168.50.30');
        }

        return $this->sentinelSshConnection;
    }

    protected function disableSentinelAt($ipAddress)
    {
        $sshConnection = new VagrantSsh($ipAddress);
        $sshConnection->execute('sudo stop sentinel');
    }

    protected function enableSentinelAt($ipAddress)
    {
        $sshConnection = new VagrantSsh($ipAddress);
        $sshConnection->execute('sudo start sentinel');
    }

    protected function debugSegfaultToMaster()
    {
        $this->getMasterSshConnection()->execute('/etc/redis/segfault_after_5s.sh &');
    }
} 
