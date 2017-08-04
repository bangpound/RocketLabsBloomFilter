<?php

namespace RocketLabs\BloomFilter\Test\Hash;

use PHPUnit\Framework\TestCase;
use RocketLabs\BloomFilter\Hash\Jenkins;

class JenkinsTest extends TestCase
{
    /**
     * @test
     */
    public function hash()
    {
        $hash = new Jenkins();
        $value = 'test value';
        $expected = '4b9f03c9478b2ae8';

        $this->assertEquals($expected, $hash->hash($value));
    }
}
