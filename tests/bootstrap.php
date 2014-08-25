<?php

// load composer generated autoloading file
require_once __DIR__.'/../vendor/autoload.php';

\Phake::setClient(\Phake::CLIENT_PHPUNIT);

// loading mocks
require_once __DIR__ . '/unit/PSRedis/Client/Adapter/Predis/Mock/AbstractMockedPredisClientCreator.php';
require_once __DIR__ . '/unit/PSRedis/Client/Adapter/Predis/Mock/MockedPredisClientCreatorWithNoMasterAddress.php';
require_once __DIR__ . '/unit/PSRedis/Client/Adapter/Predis/Mock/MockedPredisClientCreatorWithMasterAddress.php';
require_once __DIR__ . '/unit/PSRedis/Client/Adapter/Predis/Mock/MockedPredisClientCreatorWithSentinelOffline.php';
require_once __DIR__ . '/unit/PSRedis/Client/Adapter/Predis/Mock/MockedPredisClientCreatorWithFailingRedisConnection.php';
