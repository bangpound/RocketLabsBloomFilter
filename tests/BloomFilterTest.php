<?php

namespace RocketLabs\BloomFilter\Test;

use PHPUnit\Framework\TestCase;
use RocketLabs\BloomFilter\BloomFilter;
use RocketLabs\BloomFilter\Persist\Persister;

class BloomFilterTest extends TestCase
{
    /**
     * @test
     */
    public function addToFilter()
    {
        $persister = $this->getMockBuilder(Persister::class)->getMock();
        $persister->expects($this->once())
            ->method('setBulk')
            ->willReturn(1)
            ->with([687, 549, 684]); //calculated bits for hashes

        $filter = new BloomFilter($persister, 1024, 3);
        $filter->add('testString');
    }

    /**
     * @test
     */
    public function addBulkFilter()
    {
        $persister = $this->getMockBuilder(Persister::class)->getMock();
        $persister->expects($this->once())
            ->method('setBulk')
            ->willReturn(1)
            ->with([ 572, 177, 442, 128, 451, 980, 905, 698, 186]); //calculated bits for hashes

        $filter = new BloomFilter($persister, 1024, 3);
        $filter->addBulk(
            ['test String 1',
            'test String 2',
            'test String 3',
            ]
        );
    }

    /**
     * @test
     */
    public function existsInFilter()
    {
        $persister = $this->getMockBuilder(Persister::class)->getMock();
        $persister->expects($this->once())
            ->method('setBulk')
            ->willReturn(1)
            ->with([687, 549, 684]); //calculated bits for hashes
        $persister->expects($this->once())
            ->method('getBulk')
            ->willReturn([1, 1, 1])
            ->with([687, 549, 684]); //calculated bits for hashes

        $filterForSet = new BloomFilter($persister, 1024, 3);
        $filterForSet->add('testString');

        $filterForGet = new BloomFilter($persister, 1024, 3);
        $this->assertTrue($filterForGet->has('testString'));
    }

    /**
     * @test
     */
    public function DoesNotExistInFilter()
    {
        $persister = $this->getMockBuilder(Persister::class)->getMock();
        $persister->expects($this->once())
            ->method('setBulk')
            ->willReturn(1)
            ->with([1008, 193, 952]); //calculated bits for hashes
        $persister->expects($this->once())
            ->method('getBulk')
            ->willReturn([1, 0, 1])
            ->with([682, 79, 298]); //calculated bits for hashes

        $filterForSet = new BloomFilter($persister, 1024, 3);
        $filterForSet->add('test String');

        $filterForGet = new BloomFilter($persister, 1024, 3);
        $this->assertFalse($filterForGet->has('Not Existing test String'));
    }


    /**
     * @test
     * @expectedException \LogicException
     */
    public function unavailableHashes()
    {
        $persister = $this->getMockBuilder(Persister::class)->getMock();
        new BloomFilter($persister, 1024, 3, ['NotAFilter']);
    }
}
