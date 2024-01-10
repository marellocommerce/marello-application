<?php

namespace Marello\Bundle\InventoryBundle\EventListener\Doctrine;

use Marello\Bundle\InventoryBundle\Entity\InventoryLevelLogRecord;

/**
 * Trait SetsPropertyValue
 *
 * Allows to set private and protected property values of InventoryLevelLogRecord.
 *
 * @package Marello\Bundle\InventoryBundle\EventListener\Doctrine
 */
trait SetsPropertyValue
{
    /**
     * Sets property value of InventoryLevelLogRecord.
     *
     * @param InventoryLevelLogRecord    $inventoryLevelLogRecord
     * @param string            $propertyName
     * @param mixed             $value
     */
    protected function setPropertyValue(InventoryLevelLogRecord $inventoryLevelLogRecord, $propertyName, $value)
    {
        $reflection = new \ReflectionObject($inventoryLevelLogRecord);
        $property   = $reflection->getProperty($propertyName);
        $property->setValue($inventoryLevelLogRecord, $value);
    }
}
