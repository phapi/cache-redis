<?php

namespace Phapi\Tests\Cache\Redis;

use Phapi\Cache\Redis\Client;

/**
 * @coversDefaultClass \Phapi\Cache\Redis\Client
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{

    public function testConstruct()
    {
        $client = new Client();
        $this->assertInstanceOf('\Phapi\Cache\Redis\Client', $client);
    }
    public function testConstructFail()
    {
        $this->setExpectedException('\Phapi\Exception\InternalServerError');
        $client = new Client('localhost', 6376);
    }

    public function testCallSetGet()
    {
        $client = new Client();
        $client->flushdb();
        $this->assertEquals('OK', $client->set('unitTest:aKey', 'a value'));
        $this->assertEquals('a value', $client->get('unitTest:aKey'));
        $this->assertEquals('1', $client->del('unitTest:aKey'));
        $this->assertEquals(null, $client->get('unitTest:aKey'));
    }

    public function testMultiResponse()
    {
        $client = new Client();
        $this->assertEquals('1', $client->sadd('unitTest:aKey', 'value'));
        $this->assertEquals('1', $client->sadd('unitTest:aKey', 'another value'));
        $this->assertEquals('OK', $client->multi());
        $this->assertEquals('QUEUED', $client->smembers('unitTest:aKey'));
        $this->assertEquals('QUEUED', $client->smembers('unitTest:aKey'));
        $client->exec();
        $this->assertContains('another value', $client->smembers('unitTest:aKey'));
        $this->assertContains('value', $client->smembers('unitTest:aKey'));
    }

    public function testResponseException()
    {
        $client = new Client();
        $this->assertEquals('OK', $client->set('unitTest:aKey', 'the value'));
        $this->setExpectedException('\Exception');
        $client->sadd('unitTest:aKey', 'another value', 'third value');
    }
}
