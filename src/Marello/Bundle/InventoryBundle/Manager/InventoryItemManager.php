<?php

namespace Marello\Bundle\InventoryBundle\Manager;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\ProductBundle\Migrations\Data\ORM\LoadProductUnitData;

class InventoryItemManager implements InventoryItemManagerInterface
{
    /**
     * @var DoctrineHelper
     */
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
     * @param Product $product
     * @return InventoryItem|null
     */
    public function createInventoryItem($product)
    {
        if (!$product) {
            return null;
        }

        if (!$this->hasInventoryItem($product)) {
            $inventoryItem = new InventoryItem($product);
            $inventoryItem->setOrganization($product->getOrganization());
            $inventoryItem->setEnableBatchInventory(false);
            
            return $inventoryItem;
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
        return (bool) $this->getInventoryItem($product);
    }

    /**
     * get inventory item by product
     * @param $product
     * @return null|object
     */
    public function getInventoryItem($product)
    {
        if (!$product) {
            return null;
        }

        $repo = $this->doctrineHelper->getEntityRepository(InventoryItem::class);
        return $repo->findOneBy(['product' => $product->getId()]);
    }

    /**
     * Get default replenishment for InventoryItem
     * @return null|object
     */
    public function getDefaultReplenishment()
    {
        $replenishmentClass = ExtendHelper::buildEnumValueClassName('marello_inv_reple');
        $repo = $this->doctrineHelper->getEntityRepository($replenishmentClass);
        return $repo->findOneBy(['default' => 1]);
    }

    /**
     * Get default product unit for InventoryItem
     * @return null|object
     */
    public function getDefaultProductUnit()
    {
        $productUnit = ExtendHelper::buildEnumValueClassName(LoadProductUnitData::PRODUCT_UNIT_ENUM_CLASS);
        /** @var EnumValueRepository $repo */
        $repo = $this->doctrineHelper->getEntityRepository($productUnit);
        return $repo->findOneByDefault(true);
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
            /** @var InventoryItem $item */
            $item = $this->getInventoryItem($product);
            return $item;
        }

        return null;
    }
}
