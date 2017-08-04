<?php

namespace RocketLabs\BloomFilter\Persist;

/**
 * @author Igor Veremchuk igor.veremchuk@rocket-internet.de
 */
class Redis implements Persister
{
    const DEFAULT_HOST = 'localhost';
    const DEFAULT_PORT = 6379;
    const DEFAULT_DB = 0;
    const DEFAULT_KEY = 'bloom_filter';
    /** @var string */
    protected $key;
    /** @var \Redis */
    protected $redis;

    /**
     * @param array $params
     * @return static
     */
    public static function create(array $params = [])
    {
        $redis = new \Redis();

        $host = $params['host'] ?? self::DEFAULT_HOST;
        $port = $params['port'] ?? self::DEFAULT_PORT;
        $db = $params['db'] ?? self::DEFAULT_DB;
        $key = $params['key'] ?? self::DEFAULT_KEY;

        $redis->connect($host, $port);
        $redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
        $redis->select($db);

        return new static($redis, $key);
    }

    /**
     * @param \Redis $redis
     * @param string $key
     */
    public function __construct(\Redis $redis, $key)
    {
        $this->key = $key;
        $this->redis = $redis;
    }

    /**
     * @inheritdoc
     * @throws \RangeException
     * @throws \UnexpectedValueException
     */
    public function getBulk(array $bits)
    {
        $pipe = $this->redis->pipeline();

        foreach ($bits as $bit) {
            $this->assertOffset($bit);
            $pipe->getBit($this->key, $bit);
        }

        return $pipe->exec();
    }

    /**
     * @inheritdoc
     * @throws \RangeException
     * @throws \UnexpectedValueException
     */
    public function setBulk(array $bits)
    {
        $pipe = $this->redis->pipeline();

        foreach ($bits as $bit) {
            $this->assertOffset($bit);
            $pipe->setBit($this->key, $bit, 1);
        }

        $pipe->exec();
    }

    /**
     * @inheritdoc
     * @throws \RangeException
     * @throws \UnexpectedValueException
     */
    public function get($bit)
    {
        $this->assertOffset($bit);
        return $this->redis->getBit($this->key, $bit);
    }

    /**
     * @inheritdoc
     * @throws \RangeException
     * @throws \UnexpectedValueException
     */
    public function set($bit)
    {
        $this->assertOffset($bit);
        $this->redis->setBit($this->key, $bit, 1);
    }

    /**
     * @param int $value
     * @throws \UnexpectedValueException
     * @throws \RangeException
     */
    private function assertOffset($value)
    {
        if (!is_int($value)) {
            throw new \UnexpectedValueException('Value must be an integer.');
        }

        if ($value < 0) {
            throw new \RangeException('Value must be greater than zero.');
        }
    }


}