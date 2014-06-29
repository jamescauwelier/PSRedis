<?php

namespace Redis\Client;


interface SentinelClientAdapter {
    public function setIpAddress($ipAddress);
    public function setPort($port);
    public function connect();
    public function isConnected();
    public function getMaster();
} 