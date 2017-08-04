<?php

namespace RocketLabs\BloomFilter;

use RocketLabs\BloomFilter\Hash\Hash;
use RocketLabs\BloomFilter\Persist\Persister;

/**
 * @author Igor Veremchuk igor.veremchuk@rocket-internet.de
 */
class BloomFilter
{
    /** @var int */
    private $size;
    /** @var Persister */
    private $persister;
    /** @var Hash[]  */
    private $hashes;
    /** @var int */
    private $hashCount;
    /** @var array */
    private $availableHashes = ['Crc32b', 'Fnv', 'Jenkins', 'Murmur2'];

    /**
     * @param Persister $persister
     * @param int $size
     * @param int $hashCount
     * @param array $hashFunctions
     * @throws \LogicException
     */
    public function __construct(Persister $persister, $size, $hashCount, array $hashFunctions = [])
    {
        $hashFunctions = !empty($hashFunctions) ? $hashFunctions : $this->availableHashes;

        if (!array_intersect($this->availableHashes, $hashFunctions)) {
            throw new \LogicException(
                sprintf('One or more of functions (%s) are not available', implode(',', $hashFunctions))
            );
        }

        $this->persister = $persister;
        $this->size = $size;
        $this->hashCount = $hashCount;
        for ($i = 0; $i < $hashCount; $i++) {
            $hash = $hashFunctions[$i % count($hashFunctions)];
            $className = 'RocketLabs\\BloomFilter\\Hash\\' . $hash;
            $this->hashes[] = new $className;
        }
    }

    /**
     * @param string $value
     */
    public function add($value)
    {
        $this->persister->setBulk($this->getBits($value));
    }

    /**
     * @param array $valueList
     */
    public function addBulk(array $valueList)
    {
        $bits = [];
        foreach ($valueList as $value) {
            $bits[] = $this->getBits($value);
        }

        $this->persister->setBulk(array_merge(...$bits));
    }

    /**
     * @param string $value
     * @return bool
     */
    public function has($value)
    {
        $bits = $this->persister->getBulk($this->getBits($value));

        return !in_array(0, $bits, true);
    }

    /**
     * @param string $value
     * @return array
     */
    private function getBits($value)
    {
        $bits = [];
        /** @var Hash $hash */
        foreach ($this->hashes as $index => $hash) {
            $bits[] = $this->hash($hash, $value, $index);
        }

        return $bits;
    }

    /**
     * @param Hash $hash
     * @param string $value
     * @param int $index
     * @return int
     */
    private function hash(Hash $hash, $value, $index)
    {
        return crc32($hash->hash($value . $index)) % $this->size;
    }
}
