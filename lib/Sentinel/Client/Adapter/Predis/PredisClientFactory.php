<?php
/**
 * Created by PhpStorm.
 * User: jamescauwelier
 * Date: 6/28/14
 * Time: 6:17 PM
 */

namespace Sentinel\Client\Adapter\Predis;


interface PredisClientFactory
{
    public function create(array $parameters = array());
} 