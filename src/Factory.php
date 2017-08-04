<?php
/**
 * Created by PhpStorm.
 * User: bjd
 * Date: 8/3/17
 * Time: 11:39 PM
 */

namespace RocketLabs\BloomFilter;


use RocketLabs\BloomFilter\Persist\Persister;

class Factory
{
    /**
     * @param Persister $persister
     * @param int $approximateSize
     * @param float $falsePositiveProbability
     * @param array $hashFunctions
     * @return BloomFilter
     * @throws \LogicException
     * @throws \RangeException
     */
    public static function createFromApproximateSize(
        Persister $persister,
        $approximateSize,
        $falsePositiveProbability = 0.001,
        array $hashFunctions = []
    ) {
        if ($falsePositiveProbability <= 0 || $falsePositiveProbability >= 1) {
            throw new \RangeException('False positive probability must be between 0 and 1');
        }

        $bitSize = self::optimalBitSize((int) $approximateSize, $falsePositiveProbability);
        $hashCount = self::optimalHashCount($approximateSize, $bitSize);

        return new BloomFilter($persister, $bitSize, $hashCount, $hashFunctions);
    }

    /**
     * m = ceil((n * log(p)) / log(1.0 / (pow(2.0, log(2.0)))));
     * m - Number of bits in the filter
     * n - Number of items in the filter
     * p - Probability of false positives, float between 0 and 1 or a number indicating 1-in-p
     *
     * @param int $setSize
     * @param float $falsePositiveProbability
     * @return int
     */
    public static function optimalBitSize($setSize, $falsePositiveProbability = 0.001)
    {
        return (int) round((($setSize * log($falsePositiveProbability)) / (log(2) ** 2)) * -1);
    }

    /**
     * k = round(log(2.0) * m / n);
     * k - Number of hash functions
     * m - Number of bits in the filter
     * n - Number of items in the filter
     *
     * @param int $setSize
     * @param int $bitSize
     * @return int
     */
    public static function optimalHashCount($setSize, $bitSize)
    {
        return (int) round(($bitSize / $setSize) * log(2));
    }

}