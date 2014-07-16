<?php


namespace PSRedis\MasterDiscovery\BackoffStrategy;


class IncrementalTest extends \PHPUnit_Framework_TestCase
{
    public function testThatWeCanChooseTheInitialBackoffValue()
    {
        $backoff = new Incremental(0, 1);
        $this->assertEquals(0, $backoff->getBackoffInMicroSeconds(), 'We choose the initial backoff to be 0');

        $backoff = new Incremental(500000, 1);
        $this->assertEquals(500000, $backoff->getBackoffInMicroSeconds(), 'We choose the initial backoff to be 500000');

        $this->setExpectedException('\\PSRedis\\Exception\\InvalidProperty', 'The initial backoff cannot be smaller than zero');
        new Incremental(-500000, 1);
    }

    public function testBackoffWithoutMultiplying()
    {
        $backoff = new Incremental(1, 1);
        $this->assertEquals(1, $backoff->getBackoffInMicroSeconds(), 'The initial backoff without multiplying is 1');
        $this->assertEquals(1, $backoff->getBackoffInMicroSeconds(), 'The second backoff without multiplying is 1');
        $this->assertEquals(1, $backoff->getBackoffInMicroSeconds(), 'The third backoff without multiplying is 1');
    }

    public function testBackoffWithDoubling()
    {
        $backoff = new Incremental(1, 2);
        $this->assertEquals(1, $backoff->getBackoffInMicroSeconds(), 'The initial backoff with multiplying is 1');
        $this->assertEquals(2, $backoff->getBackoffInMicroSeconds(), 'The second backoff with multiplying is 2');
        $this->assertEquals(4, $backoff->getBackoffInMicroSeconds(), 'The third backoff with multiplying is 4');
        $this->assertEquals(8, $backoff->getBackoffInMicroSeconds(), 'The fourth backoff with multiplying is 8');
    }

    public function testBackoffWithSlowerIncrementer()
    {
        $backoff = new Incremental(2, 1.5);
        $this->assertEquals(2, $backoff->getBackoffInMicroSeconds(), 'The initial backoff with slower multiplying is 2');
        $this->assertEquals(3, $backoff->getBackoffInMicroSeconds(), 'The second backoff with slower multiplying is 3');
        $this->assertEquals(4.5, $backoff->getBackoffInMicroSeconds(), 'The third backoff with slower multiplying is 4.5');
        $this->assertEquals(6.75, $backoff->getBackoffInMicroSeconds(), 'The fourth backoff with slower multiplying is 6.75');
    }

    public function testBackoffAfterAReset()
    {
        $backoff = new Incremental(1, 2);
        $backoff->getBackoffInMicroSeconds();
        $backoff->getBackoffInMicroSeconds();
        $backoff->getBackoffInMicroSeconds();
        $backoff->getBackoffInMicroSeconds();
        $backoff->reset();
        $this->assertEquals(1, $backoff->getBackoffInMicroSeconds(), 'After a reset, the initial backoff is returned again');
    }

    public function testBackoffMaxAttempts()
    {
        $backoff = new Incremental(1, 2);
        $backoff->setMaxAttempts(1);
        $this->assertTrue($backoff->shouldWeTryAgain(), 'We are allowed to backoff the first time');
        $backoff->getBackoffInMicroSeconds();
        $this->assertFalse($backoff->shouldWeTryAgain(), 'We are not allowed to backoff a second time');
        $backoff->reset();
        $this->assertTrue($backoff->shouldWeTryAgain(), 'We are allowed to backoff again after a reset');
    }

    public function testNeverBackingOff()
    {
        $backoff = new Incremental(0, 0);
        $backoff->setMaxAttempts(0);
        $this->assertFalse($backoff->shouldWeTryAgain(), 'We should never be retrying again');
    }
}
 