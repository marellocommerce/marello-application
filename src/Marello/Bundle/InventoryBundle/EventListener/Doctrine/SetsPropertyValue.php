<?php

namespace Marello\Bundle\InventoryBundle\EventListener\Doctrine;

use Marello\Bundle\InventoryBundle\Entity\StockLevel;

/**
 * Trait SetsPropertyValue
 *
 * Allows to set private and protected property values of StockLevel.
 *
 * @package Marello\Bundle\InventoryBundle\EventListener\Doctrine
 */
trait SetsPropertyValue
{
    /**
     * Sets property value of StockLevel.
     *
     * @param StockLevel $stockLevel
     * @param string     $propertyName
     * @param mixed      $value
     */
    protected function setPropertyValue(StockLevel $stockLevel, $propertyName, $value)
    {
        $reflection = new \ReflectionObject($stockLevel);
        $property   = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($stockLevel, $value);
    }
}
