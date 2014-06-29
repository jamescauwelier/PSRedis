<?php

namespace Redis;

use Redis\Client\Adapter\Predis\PredisClientCreator;
use Redis\Exception\InvalidProperty;
use Redis\Client\Adapter\PredisSentinelClientAdapter;
use Redis\Client\SentinelClientAdapter;
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

    public function __construct($ipAddress, $port, SentinelClientAdapter $uninitializedClientAdapter = null)
    {
        $this->guardThatIpAddressFormatIsValid($ipAddress);
        $this->guardThatServerPortIsValid($port);

        $this->ipAddress = $ipAddress;
        $this->port = $port;

        if (empty($uninitializedClientAdapter)) {
            $uninitializedClientAdapter = new PredisSentinelClientAdapter(new PredisClientCreator());
        }
        $this->clientAdapter = $this->initializeClientAdapter($uninitializedClientAdapter);
    }

    private function initializeClientAdapter(SentinelClientAdapter $clientAdapter)
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

    public function getMaster()
    {
        return $this->clientAdapter->getMaster();
    }

    public function isMaster()
    {
        return false;
    }
} 