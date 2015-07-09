<?php

namespace Phapi\Tests\Cache\Redis;

use Phapi\Cache\Redis\Redis;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @coversDefaultClass \Phapi\Cache\Redis\Redis
 */
class RedisTest extends TestCase
{

    protected $cache;
    protected $key;
    protected $value;
    protected $replace;

    public function setUp()
    {
        $this->cache = new Redis([['host' => 'localhost', 'port' => 6379]]);
        $this->key = 'test_'. time();
        $this->value = 'some test value';
        $this->replace = 'replaced test value';
    }

    public function testConstructor()
    {
        $this->cache = new Redis([['host' => 'localhost', 'port' => 6379]]);
        $this->assertTrue($this->cache->flush());
    }

    public function testNotConnected()
    {
        $this->setExpectedException('Exception', 'Unable to connect to Redis backend');
        $this->cache = new Redis([['host' => 'localhost', 'port' => 6376]]);
    }

    /**
     * @depends testConstructor
     */
    public function testSetGet()
    {
        // set a key and a value
        $this->assertTrue($this->cache->set($this->key, $this->value));

        // get the value based on the key and validate it
        $this->assertEquals($this->value, $this->cache->get($this->key));

        $this->assertTrue($this->cache->set($this->key, $this->replace));
        $this->assertEquals($this->replace, $this->cache->get($this->key));
    }

    /**
     * @depends testConstructor
     */
    public function testHas()
    {
        // set a key and a value
        $this->assertTrue($this->cache->set($this->key, $this->value));

        // check if the key exists
        $this->assertTrue($this->cache->has($this->key));
    }

    /**
     * @depends testConstructor
     */
    public function testClear()
    {
        // set a key and a value
        $this->assertTrue($this->cache->set($this->key, $this->value));

        // remove key from cache
        $this->assertTrue($this->cache->clear($this->key));

        // check if the key exists
        $this->assertEmpty($this->cache->get($this->key));
    }
}
