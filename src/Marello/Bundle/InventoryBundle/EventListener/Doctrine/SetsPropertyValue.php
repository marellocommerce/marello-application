<?php

namespace Marello\Bundle\InventoryBundle\EventListener\Doctrine;

use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;

/**
 * Trait SetsPropertyValue
 *
 * Allows to set private and protected property values of InventoryLevel.
 *
 * @package Marello\Bundle\InventoryBundle\EventListener\Doctrine
 */
trait SetsPropertyValue
{
    /**
     * Sets property value of InventoryLevel.
     *
     * @param InventoryLevel    $inventoryLevel
     * @param string            $propertyName
     * @param mixed             $value
     */
    protected function setPropertyValue(InventoryLevel $inventoryLevel, $propertyName, $value)
    {
        $reflection = new \ReflectionObject($inventoryLevel);
        $property   = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($inventoryLevel, $value);
    }
}
