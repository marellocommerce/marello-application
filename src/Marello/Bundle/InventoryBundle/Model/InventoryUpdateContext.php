<?php

namespace Marello\Bundle\InventoryBundle\Model;

use Oro\Bundle\UserBundle\Entity\UserInterface;

class InventoryUpdateContext
{
    /** @var array $values */
    private $values = [];

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

    public function getItems()
    {
        return $this->getValue('items');
    }

    public function setItems($items)
    {
        $this->setValue('items', $items);
    }

    public function setRelatedEntity($entity)
    {
        $this->setValue('related_entity', $entity);
    }

    public function getRelatedEntity()
    {
        return $this->getValue('related_entity');
    }

    public function setUser(UserInterface $user)
    {
        $this->setValue('user', $user);
    }

    public function getUser()
    {
        return $this->getValue('user');
    }

    public function setChangeTrigger($trigger)
    {
        $this->setValue('change_trigger', $trigger);
    }

    public function getChangeTrigger()
    {
        return $this->getValue('change_trigger');
    }

    public function setAllocatedStock($allocatedStock)
    {
        $this->setValue('allocated_stock', $allocatedStock);
    }

    public function getAllocatedStock()
    {
        return $this->getValue('allocated_stock');
    }

    public function setStock($stock)
    {
        $this->setValue('stock', $stock);
    }

    public function getStock()
    {
        return $this->getValue('stock');
    }

    public function setProduct($product)
    {
        $this->setValue('product', $product);
    }

    public function getProduct()
    {
        return $this->getValue('product');
    }

    public static function createUpdateContext(array $data)
    {
        if (!array_key_exists('stock', $data)) {
            return null;
        }

        if (!array_key_exists('allocatedStock', $data)) {
            return null;
        }

        if (!array_key_exists('trigger', $data)) {
            return null;
        }

        if (!array_key_exists('item', $data)) {
            return null;
        }

        $context = new self();
        $context->setStock($data['stock']);
        $context->setAllocatedStock($data['allocatedStock']);
        $context->setChangeTrigger($data['trigger']);
        $context->setProduct($data['item']->getProduct());

        return $context;
    }
}
