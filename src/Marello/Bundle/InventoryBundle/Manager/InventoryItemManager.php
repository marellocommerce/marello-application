<?php

namespace Marello\Bundle\InventoryBundle\Manager;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;

class InventoryItemManager implements InventoryItemManagerInterface
{
    /** @var DoctrineHelper $doctrineHelper */
    protected $doctrineHelper;

    /**
     * InventoryItemManager constructor.
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper   = $doctrineHelper;
    }

    /**
     * @param $product
     * @return InventoryItem|null
     */
    public function createInventoryItem($product)
    {
        if (!$product) {
            return null;
        }

        if (!$this->hasInventoryItem($product)) {
            //set a default warehouse for inventory item to prevent BC break until the field $warehouse is removed
            $defaultWarehouse = $this->doctrineHelper->getEntityRepository(Warehouse::class)->getDefault();
            return new InventoryItem($defaultWarehouse, $product);
        }

        return null;
    }

    /**
     * Check if product already has an InventoryItem assigned
     * @param $product
     * @return bool
     */
    public function hasInventoryItem($product)
    {
        $repo = $this->doctrineHelper->getEntityRepository(InventoryItem::class);
        return (bool) $repo->findOneBy(['product' => $product->getId()]);
    }

    /**
     * Get default replenishment for InventoryItem
     * @return null|object
     */
    public function getDefaultReplenishment()
    {
        $replenishmentClass = ExtendHelper::buildEnumValueClassName('marello_product_reple');
        $repo = $this->doctrineHelper->getEntityRepository($replenishmentClass);
        return $repo->findOneBy(['default' => 1]);
    }

    /**
     * Get an InventoryItem to delete based on the Product
     * @param $product
     * @return InventoryItem|null
     */
    public function getInventoryItemToDelete($product)
    {
        if (!$product) {
            return null;
        }

        if ($this->hasInventoryItem($product)) {
            $repo = $this->doctrineHelper->getEntityRepository(InventoryItem::class);
            /** @var InventoryItem $item */
            $item = $repo->findOneBy(['product' => $product->getId()]);
            return $item;
        }

        return null;
    }
}
