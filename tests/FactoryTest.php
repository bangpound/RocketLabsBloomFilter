<?php

namespace RocketLabs\BloomFilter\Test;

use PHPUnit\Framework\TestCase;
use RocketLabs\BloomFilter\BloomFilter;
use RocketLabs\BloomFilter\Factory;
use RocketLabs\BloomFilter\Persist\Persister;
use RocketLabs\BloomFilter\Persist\Redis;

class FactoryTest extends TestCase
{
    /**
     * @test
     */
    public function optimalBitSize()
    {
        $n = 100; // Number fo items
        $p = 0.001; // Probability of false positives

        $this->assertEquals(
            round((($n * log($p)) / (log(2) ** 2)) * -1),
            Factory::optimalBitSize($n, $p)
        );
    }

    /**
     * @test
     */
    public function optimalHashCount()
    {
        $n = 100; // Number fo items
        $m = 1024; // Number fo bits

        $this->assertEquals(
            (int) round(($n / $m) * log(2)),
            Factory::optimalHashCount($m, $n)
        );
    }

    /**
     * @param int $size
     * @param float $probability
     * @param int $expectedHashSize
     * @param int $expectedBitSize
     *
     * @test
     * @dataProvider createFromApproximateSizeDataProvider
     */
    public function createFromApproximateSize($size, $probability, $expectedHashSize, $expectedBitSize)
    {
        $redisMock = $this->getMockBuilder(\Redis::class)->getMock();
        $persister = new Redis($redisMock, Redis::DEFAULT_KEY);

        $class = new \ReflectionClass(BloomFilter::class);
        $propertyHashes = $class->getProperty('hashes');
        $propertySize = $class->getProperty('size');
        $propertyHashes->setAccessible(true);
        $propertySize->setAccessible(true);
        $filter = Factory::createFromApproximateSize($persister, $size, $probability);

        $this->assertCount($expectedHashSize, $propertyHashes->getValue($filter));
        $this->assertEquals($expectedBitSize, $propertySize->getValue($filter));
    }

    /**
     * @return array
     */
    public function createFromApproximateSizeDataProvider()
    {
        return [
            'Size: 100, probability: 99.9%' => [
                '$size' => 100,
                '$probability' => 0.001,
                '$expectedHashSize' => 10,
                '$expectedBitSize' => 1438
            ],
            'Size: 1000, probability: 99%' => [
                '$size' => 1000,
                '$probability' => 0.01,
                '$expectedHashSize' => 7,
                '$expectedBitSize' => 9585
            ],
            'Size: 1000, probability: 99,99%' => [
                '$size' => 1000,
                '$probability' => 0.0001,
                '$expectedHashSize' => 13,
                '$expectedBitSize' => 19170
            ],
            'Size: 1000000, probability: 99,99%' => [
                '$size' => 1000000,
                '$probability' => 0.0001,
                '$expectedHashSize' => 13,
                '$expectedBitSize' => 19170117 //2.3Mb
            ]
        ];
    }

    /**
     * @test
     * @expectedException \RangeException
     */
    public function createFromApproximateSizeOutOfRange()
    {
        $persister = $this->getMockBuilder(Persister::class)->getMock();
        Factory::createFromApproximateSize($persister, 1024, 2);
    }
}