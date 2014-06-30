<?php

namespace Redis\Client;


interface ClientAdapter {
    public function setIpAddress($ipAddress);
    public function setPort($port);
    public function connect();
    public function isConnected();
    public function getMaster($nameOfNodeSet);
    public function getRole();
} 