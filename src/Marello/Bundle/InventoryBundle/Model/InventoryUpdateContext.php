<?php

namespace Marello\Bundle\InventoryBundle\Model;

use Oro\Bundle\UserBundle\Entity\UserInterface;

class InventoryUpdateContext
{
    /** @var array $values */
    private $values;

    public function __construct()
    {
        $this->values = [];
    }

    /**
     * {@inheritdoc}
     * @param $name
     * @param $value
     */
    public function setValue($name, $value)
    {
        $this->values[$name] = $value;
    }

    /**
     * {@inheritdoc}
     * @param $name
     * @return mixed|null
     */
    public function getValue($name)
    {
        return isset($this->values[$name])
            ? $this->values[$name]
            : null;
    }

    /**
     * @deprecated
     * @param $items
     * @return $this
     */
    public function setItems($items)
    {
        $this->setValue('items', $items);

        return $this;
    }

    /**
     * @deprecated
     * @return mixed|null
     */
    public function getItems()
    {
        return $this->getValue('items');
    }

    /**
     * {@inheritdoc}
     * @param $item
     * @return $this
     */
    public function setInventoryItem($item)
    {
        $this->setValue('inventory_item', $item);

        return $this;
    }

    /**
     * {@inheritdoc}
     * @return mixed|null
     */
    public function getInventoryItem()
    {
        return $this->getValue('inventory_item');
    }

    /**
     * {@inheritdoc}
     * @param $item
     * @return $this
     */
    public function setInventoryLevel($inventoryLevel)
    {
        $this->setValue('inventory_level', $inventoryLevel);

        return $this;
    }

    /**
     * {@inheritdoc}
     * @return mixed|null
     */
    public function getInventoryLevel()
    {
        return $this->getValue('inventory_level');
    }

    /**
     * {@inheritdoc}
     * @param $entity
     * @return $this
     */
    public function setRelatedEntity($entity)
    {
        $this->setValue('related_entity', $entity);

        return $this;
    }

    /**
     * {@inheritdoc}
     * @return mixed|null
     */
    public function getRelatedEntity()
    {
        return $this->getValue('related_entity');
    }

    /**
     * {@inheritdoc}
     * @param UserInterface $user
     * @return $this
     */
    public function setUser(UserInterface $user)
    {
        $this->setValue('user', $user);

        return $this;
    }

    /**
     * {@inheritdoc}
     * @return mixed|null
     */
    public function getUser()
    {
        return $this->getValue('user');
    }

    /**
     * {@inheritdoc}
     * @param $trigger
     * @return $this
     */
    public function setChangeTrigger($trigger)
    {
        $this->setValue('change_trigger', $trigger);

        return $this;
    }

    /**
     * {@inheritdoc}
     * @return mixed|null
     */
    public function getChangeTrigger()
    {
        return $this->getValue('change_trigger');
    }

    /**
     * @deprecated use setAllocatedInventoryQty instead
     * @param $allocatedQty
     * @return $this
     */
    public function setAllocatedStock($allocatedQty)
    {
        return $this->setAllocatedInventory($allocatedQty);
    }

    /**
     * @param $allocatedQty
     * @return $this
     */
    public function setAllocatedInventory($allocatedQty)
    {
        $this->setValue('allocated_inventory_qty', $allocatedQty);

        return $this;
    }

    /**
     * @deprecated use getAllocatedInventory instead
     * @return mixed|null
     */
    public function getAllocatedStock()
    {
        return $this->getAllocatedInventory();
    }

    public function getAllocatedInventory()
    {
        return $this->getValue('allocated_inventory_qty');
    }

    /**
     * @deprecated use setInventory($qty) instead
     * @param $stock
     * @return $this
     */
    public function setStock($stock)
    {
        return $this->setInventory($stock);
    }

    /**
     * @deprecated use getInventory() instead
     * @return mixed|null
     */
    public function getStock()
    {
        return $this->getInventory();
    }

    /**
     * {@inheritdoc}
     * @param $qty
     * @return $this
     */
    public function setInventory($qty)
    {
        $this->setValue('quantity', $qty);

        return $this;
    }

    /**
     * {@inheritdoc}
     * @return mixed|null
     */
    public function getInventory()
    {
        return $this->getValue('quantity');
    }

    /**
     * {@inheritdoc}
     * @param $product
     * @return $this
     */
    public function setProduct($product)
    {
        $this->setValue('product', $product);

        return $this;
    }

    /**
     * {@inheritdoc}
     * @return mixed|null
     */
    public function getProduct()
    {
        return $this->getValue('product');
    }

    /**
     * {@inheritdoc}
     * @param bool $isVirtual
     * @return $this
     */
    public function setIsVirtual($isVirtual)
    {
        $this->setValue('is_virtual', $isVirtual);

        return $this;
    }

    /**
     * {@inheritdoc}
     * @return bool|null
     */
    public function getIsVirtual()
    {
        return $this->getValue('is_virtual');
    }
}
