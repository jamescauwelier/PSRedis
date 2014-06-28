<?php

namespace Sentinel\Client\Adapter;

use Sentinel\Client\SentinelClientAdapter;

class NullSentinelClientAdapter
    extends AbstractSentinelClientAdapter
    implements SentinelClientAdapter
{

    public function connect()
    {

    }

}