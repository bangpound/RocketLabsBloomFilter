<?php

namespace RocketLabs\BloomFilter\Test\Hash;

use PHPUnit\Framework\TestCase;
use RocketLabs\BloomFilter\Hash\Murmur2;

class Murmur2Test extends TestCase
{
    /**
     * @test
     */
    public function hash()
    {
        $hash = new Murmur2();
        $value = 'test value';
        $expected = 932882152;

        $this->assertEquals($expected, $hash->hash($value));
    }
}
