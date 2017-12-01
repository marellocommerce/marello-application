<?php

namespace Marello\Bundle\CustomerBundle\Tests\Unit\Entity;

use Marello\Bundle\TaxBundle\Model\Result;
use Marello\Bundle\TaxBundle\Model\ResultElement;

class ResultTest extends \PHPUnit_Framework_TestCase
{
    public function testProperties()
    {
        $result = $this->createResultModel();

        $this->assertInstanceOf(ResultElement::class, $result->getTotal());
        $this->assertInstanceOf(ResultElement::class, $result->getShipping());
        $this->assertInternalType('array', $result->getItems());

        $this->assertEquals($this->createTotal(), $result->getTotal());
        $this->assertEquals($this->createShipping(), $result->getShipping());
        $this->assertEquals($this->createItemsResult(), $result->getItems());

        $this->assertCount(3, $result);
        $expected = [
            'total' => $this->createTotal(),
            'shipping' => $this->createShipping(),
            'items' => $this->createItemsResult(),
        ];

        foreach ($result as $key => $value) {
            $this->assertArrayHasKey($key, $expected);
            $this->assertEquals($expected[$key], $value);
        }
    }

    /**
     * @return Result
     */
    protected function createResultModel()
    {
        return new Result(
            [
                Result::TOTAL => $this->createTotal(),
                Result::SHIPPING => $this->createShipping(),
                Result::ITEMS => $this->createItemsResult(),
            ]
        );
    }

    /**
     * @return array
     */
    protected function createItemsResult()
    {
        return [new Result()];
    }

    /**
     * @return ResultElement
     */
    protected function createTotal()
    {
        return ResultElement::create(1, 2, 3);
    }

    /**
     * @return ResultElement
     */
    protected function createShipping()
    {
        return ResultElement::create(5, 6, 7);
    }

    public function testConstruct()
    {
        $this->assertEquals(
            $this->createResultModel(),
            new Result(
                [
                    Result::TOTAL => new ResultElement(
                        [
                            ResultElement::INCLUDING_TAX => 1,
                            ResultElement::EXCLUDING_TAX => 2,
                            ResultElement::TAX_AMOUNT => 3,
                        ]
                    ),
                    Result::SHIPPING => $this->createShipping(),
                    Result::ITEMS => $this->createItemsResult(),
                ]
            )
        );
    }

    public function testSerializeItemsDropped()
    {
        $result = $this->createResultModel();

        /** @var Result $newResult */
        $newResult = unserialize(serialize($result));
        $this->assertEquals([], $newResult->getItems());
        $this->assertNotEmpty($result->getItems());
    }

    public function testSerializeWithoutItems()
    {
        $result = $this->createResultModel();

        /** @var Result $newResult */
        $newResult = unserialize(serialize($result));
        $this->assertEquals([], $newResult->getItems());
    }


    public function testJsonSerializeItemsDropped()
    {
        $result = $this->createResultModel();

        /** @var Result $newResult */
        $newResult = Result::jsonDeserialize(json_decode(json_encode($result), true));
        $this->assertEquals([], $newResult->getItems());
        $this->assertNotEmpty($result->getItems());
    }

    public function testJsonSerializeWithoutItems()
    {
        $result = $this->createResultModel();

        /** @var Result $newResult */
        $newResult = Result::jsonDeserialize(json_decode(json_encode($result), true));
        $this->assertEquals([], $newResult->getItems());
    }
}
