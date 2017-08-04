<?php

namespace RocketLabs\BloomFilter\Hash;

/**
 * @author Igor Veremchuk igor.veremchuk@rocket-internet.de
 */
class Fnv implements Hash
{
    /**
     * @inheritdoc
     */
    public function hash($value):string
    {
        return sprintf('%u', hexdec(hash('fnv132', $value)));
    }
}
