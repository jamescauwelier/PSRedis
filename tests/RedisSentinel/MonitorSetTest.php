<?php

namespace RedisSentinel;


class MonitorSetTest extends \PHPUnit_Framework_TestCase
{
    private $monitorSetName = 'name-of-monitor-set';

    public function testAMonitorSetHasAName()
    {
        $monitorSet = new MonitorSet($this->monitorSetName);
        $this->assertEquals($this->monitorSetName, $monitorSet->getName(), 'A monitor set is identified by a name');
    }

}
 