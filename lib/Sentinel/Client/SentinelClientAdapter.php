<?php

namespace Sentinel\Client;


interface SentinelClientAdapter {
    public function setIpAddress($ipAddress);
    public function setPort($port);
    public function connect();
    public function isConnected();
} 