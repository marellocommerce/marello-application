<?php

namespace Marello\Bundle\TaxBundle\Tests;

use Marello\Bundle\TaxBundle\Model\AbstractResult;
use Marello\Bundle\TaxBundle\Model\Result;

/**
 * @method void assertEquals($expected, $actual, $message = '')
 * @method void assertTrue($condition, $message = '')
 */
trait ResultComparatorTrait
{
    /**
     * @param array|AbstractResult $resultElement
     * @return array
     */
    protected function extractScalarValues($resultElement)
    {
        if (is_string($resultElement)) {
            return $resultElement;
        }

        if (is_numeric($resultElement)) {
            return (string)$resultElement;
        }

        if ($resultElement instanceof AbstractResult) {
            $resultElement = $resultElement->getArrayCopy();
        }

        if (is_array($resultElement)) {
            foreach ($resultElement as &$element) {
                $element = $this->extractScalarValues($element);
            }
        }

        return $resultElement;
    }

    /**
     * @param Result|array $expected
     * @param Result|array $actual
     */
    protected function compareResult($expected, $actual)
    {
        $expected = $this->extractScalarValues($expected);
        $actual = $this->extractScalarValues($actual);

        if (!$expected) {
            $this->assertEquals([], $actual);

            return;
        }

        $this->assertEquals($expected, $expected);
    }
}
