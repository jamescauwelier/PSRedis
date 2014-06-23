<?php


namespace RedisSentinel;


class Client 
{
    /**
     * @var SentinelNode
     */
    private $node;

    public function __construct (SentinelNode $node)
    {
        $this->node = $node;
    }
} 