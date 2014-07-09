<?php


namespace Redis;


class ConnectingTest extends Redis_Integration_TestCase
{
    public function testThatWeCanConnectToSentinelsAndInspectTheirRole()
    {
        $sentinel1 = new Client('192.168.50.40', '26379');
        $sentinel2 = new Client('192.168.50.41', '26379');
        $sentinel3 = new Client('192.168.50.30', '26379');

        for ($i = 1; $i <= 3; $i++) {
            $nodeName = 'sentinel'.$i;
            $this->assertEquals(Client::ROLE_SENTINEL, ${$nodeName}->getRoleType(), 'The role returned by sentinel '.$i.' is "sentinel"');
            $this->assertTrue(${$nodeName}->isSentinel(), 'Verify that sentinel '.$i.' is a sentinel');
        }
    }

    public function testThatWeCanConnectToMasterAndInspectTheRole()
    {
        $master = new Client('192.168.50.40', '6379', null, Client::TYPE_REDIS);
        $this->assertEquals(Client::ROLE_MASTER, $master->getRoleType(), 'The master should be identified with that type');
        $this->assertTrue($master->isMaster(), 'Verify the master is a master');
    }

    public function testThatWeCanConnectToSlaveAndInspectTheRole()
    {
        $slave = new Client('192.168.50.41', '6379', null, Client::TYPE_REDIS);
        $this->assertEquals(Client::ROLE_SLAVE, $slave->getRoleType(), 'The slave should be identified with that type');
        $this->assertTrue($slave->isSlave(), 'Verify the slave is a slave');
    }
}
 