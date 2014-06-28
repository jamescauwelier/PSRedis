<?php
/**
 * Created by PhpStorm.
 * User: jamescauwelier
 * Date: 6/27/14
 * Time: 5:24 PM
 */

namespace RedisSentinel\RedisClient;


interface Adapter {
    public function setIpAddress($ipAddress);
    public function setPort($port);
    public function connect();
    public function isConnected();
} 