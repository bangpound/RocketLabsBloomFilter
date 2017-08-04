<?php

namespace RocketLabs\BloomFilter\Test\Hash;

use PHPUnit\Framework\TestCase;
use RocketLabs\BloomFilter\Hash\Crc32b;

class Crc32bTest extends TestCase
{
    /**
     * @test
     */
    public function hash()
    {
        $hash = new Crc32b();
        $value = 'test value';
        $expected =  hexdec(hash('crc32b', $value));

        $this->assertEquals($expected, $hash->hash($value));
    }
}
