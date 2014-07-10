<?php

namespace Redis;

use Redis\Client\Adapter\Predis\PredisClientCreator;
use Redis\Exception\InvalidProperty;
use Redis\Client\Adapter\PredisClientAdapter;
use Redis\Client\ClientAdapter;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Ip;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Validation;

/**
 * Class Client
 *
 * Represents one single sentinel node and provides identification if we want to connect to it
 *
 * @package Sentinel
 */
class Client
{
    /**
     * @var string
     */
    private $ipAddress;

    /**
     * @var integer
     */
    private $port;

    private $clientAdapter;

    const TYPE_SENTINEL = 'sentinel';
    const TYPE_REDIS    = 'redis';

    const ROLE_SENTINEL = 'sentinel';
    const ROLE_MASTER   = 'master';
    const ROLE_SLAVE    = 'slave';

    public function __construct($ipAddress, $port, ClientAdapter $uninitializedClientAdapter = null, $connectionType = self::TYPE_SENTINEL)
    {
        $this->guardThatIpAddressFormatIsValid($ipAddress);
        $this->guardThatServerPortIsValid($port);

        $this->ipAddress = $ipAddress;
        $this->port = $port;

        if (empty($uninitializedClientAdapter)) {
            $uninitializedClientAdapter = new PredisClientAdapter(new PredisClientCreator(), $connectionType);
        }
        $this->clientAdapter = $this->initializeClientAdapter($uninitializedClientAdapter);
    }

    private function initializeClientAdapter(ClientAdapter $clientAdapter)
    {
        $clientAdapter->setIpAddress($this->getIpAddress());
        $clientAdapter->setPort($this->getPort());

        return $clientAdapter;
    }

    /**
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Validates that the proper IP address format is used when constructing the sentinel node
     * @param $ipAddress
     * @throws Exception\InvalidProperty
     */
    private function guardThatIpAddressFormatIsValid($ipAddress)
    {
        $ipValidator = Validation::createValidator();
        $violations = $ipValidator->validateValue($ipAddress, new Ip());
        if ($violations->count() > 0) {
            throw new InvalidProperty('A sentinel node requires a valid IP address');
        }
    }

    /**
     * @param $port
     * @throws Exception\InvalidProperty
     */
    private function guardThatServerPortIsValid($port)
    {
        $validator = Validation::createValidator();
        $violations = $validator->validateValue($port, new Range(array('min' => 0, 'max' => 65535)));
        if ($violations->count() > 0) {
            throw new InvalidProperty('A sentinel node requires a valid service port');
        }
    }

    public function connect()
    {
        $this->clientAdapter->connect();
    }

    public function isConnected()
    {
        return $this->clientAdapter->isConnected();
    }

    public function getClientAdapter()
    {
        return $this->clientAdapter;
    }

    /**
     * @return \Redis\Client
     */
    public function getMaster($nameOfNodeSet)
    {
        return $this->clientAdapter->getMaster($nameOfNodeSet);
    }

    public function getRole()
    {
        return $this->clientAdapter->getRole();
    }

    public function getRoleType()
    {
        $role = $this->getRole();
        return $role[0];
    }

    public function isMaster()
    {
        // Doesn't work with Redis < 2.8.12
        return true;
        // return $this->getRoleType() === Client::ROLE_MASTER;
    }

    public function isSentinel()
    {
        return $this->getRoleType() === Client::ROLE_SENTINEL;
    }

    public function isSlave()
    {
        return $this->getRoleType() === Client::ROLE_SLAVE;
    }
} 
