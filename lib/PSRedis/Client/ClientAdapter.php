<?php

namespace PSRedis\Client;

/**
 * Interface ClientAdapter
 *
 * Implement this to make sure we have everything we need to talk to Sentinel and Redis clients.
 *
 * @package PSRedis\Client
 */
interface ClientAdapter {

    /**
     * IP address of the redis or sentinel connection
     * @param $ipAddress
     * @return mixed
     */
    public function setIpAddress($ipAddress);

    /**
     * Port of redis or sentinel connection
     * @param $port
     * @return mixed
     */
    public function setPort($port);

    /**
     * Proxy to connection mechanism of the redis client
     * @return mixed
     */
    public function connect();

    /**
     * Proxy to the client implementation to verify connection status
     * @return mixed
     */
    public function isConnected();

    /**
     * Asks a sentinel instance where the master lives (IP address and port)
     * @param $nameOfNodeSet
     * @return mixed
     */
    public function getMaster($nameOfNodeSet);

    /**
     * This inspects the role of a server
     * @see http://redis.io/commands/role
     * @return mixed
     */
    public function getRole();

    /**
     * This proxies actual redis command calls to the redis client implementation
     * @param $methodName
     * @param array $methodParameters
     * @return mixed
     */
    public function __call($methodName, array $methodParameters = array());
} 