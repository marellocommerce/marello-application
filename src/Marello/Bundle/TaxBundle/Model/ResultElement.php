<?php

namespace Marello\Bundle\TaxBundle\Model;

final class ResultElement extends AbstractResultElement implements \JsonSerializable
{
    const INCLUDING_TAX = 'includingTax';
    const EXCLUDING_TAX = 'excludingTax';
    const TAX_AMOUNT = 'taxAmount';

    /**
     * @param string $includingTax
     * @param string $excludingTax
     * @param string|int $taxAmount
     *
     * @return ResultElement
     */
    public static function create(
        $includingTax,
        $excludingTax,
        $taxAmount = null
    ) {
        $resultElement = new static;

        $resultElement->offsetSet(self::INCLUDING_TAX, $includingTax);
        $resultElement->offsetSet(self::EXCLUDING_TAX, $excludingTax);
        if (null !== $taxAmount) {
            $resultElement->offsetSet(self::TAX_AMOUNT, $taxAmount);
        }

        return $resultElement;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->getArrayCopy();
    }

    /**
     * @return string
     */
    public function getIncludingTax()
    {
        return $this->getOffset(self::INCLUDING_TAX);
    }

    /**
     * @return string
     */
    public function getExcludingTax()
    {
        return $this->getOffset(self::EXCLUDING_TAX);
    }

    /**
     * @return string
     */
    public function getTaxAmount()
    {
        return $this->getOffset(self::TAX_AMOUNT, 0);
    }
}
