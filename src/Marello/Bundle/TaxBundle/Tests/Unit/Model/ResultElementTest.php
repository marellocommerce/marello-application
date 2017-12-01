<?php

namespace Marello\Bundle\CustomerBundle\Tests\Unit\Entity;

use Marello\Bundle\TaxBundle\Model\ResultElement;

class ResultElementTest extends \PHPUnit_Framework_TestCase
{
    const INCLUDING_TAX = 1.2;
    const EXCLUDING_TAX = 1;
    const TAX_AMOUNT = 0.2;

    public function testProperties()
    {
        $resultElement = $this->createResultElementModel();
        $this->assertEquals(static::INCLUDING_TAX, $resultElement->getIncludingTax());
        $this->assertEquals(static::EXCLUDING_TAX, $resultElement->getExcludingTax());
        $this->assertEquals(static::TAX_AMOUNT, $resultElement->getTaxAmount());

        $this->assertCount(3, $resultElement);
        $expected = [
            'includingTax' => self::INCLUDING_TAX,
            'excludingTax' => self::EXCLUDING_TAX,
            'taxAmount' => self::TAX_AMOUNT,
        ];

        foreach ($resultElement as $key => $value) {
            $this->assertArrayHasKey($key, $expected);
            $this->assertEquals($expected[$key], $value);
        }
    }

    /**
     * @return ResultElement
     */
    protected function createResultElementModel()
    {
        return ResultElement::create(
            static::INCLUDING_TAX,
            static::EXCLUDING_TAX,
            static::TAX_AMOUNT
        );
    }

    public function testConstruct()
    {
        $this->assertEquals(
            $this->createResultElementModel(),
            new ResultElement(
                [
                    ResultElement::EXCLUDING_TAX => static::EXCLUDING_TAX,
                    ResultElement::INCLUDING_TAX => static::INCLUDING_TAX,
                    ResultElement::TAX_AMOUNT => static::TAX_AMOUNT
                ]
            )
        );
    }
}
