<?php

namespace Marello\Bundle\InventoryBundle\ImportExport\TemplateFixture;

use Marello\Bundle\InventoryBundle\Entity\InventoryBatch;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ImportExportBundle\TemplateFixture\AbstractTemplateRepository;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateFixtureInterface;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;

class InventoryLevelFixture extends AbstractTemplateRepository implements TemplateFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEntityClass()
    {
        return InventoryLevel::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->getEntityData('sku_001');
    }

    /**
     * {@inheritdoc}
     */
    protected function createEntity($key)
    {
        $warehouseRepo = $this->templateManager
            ->getEntityRepository(Warehouse::class);

        $product = $this->createProduct();
        $warehouse = $warehouseRepo->getEntity('default');
        $inventoryItem = new InventoryItem($warehouse, $product);
        $inventoryItem->setEnableBatchInventory(true);

        $inventoryBatch = new InventoryBatch();
        $inventoryBatch
            ->setBatchNumber('aaa-bbb-ccc-1')
            ->setQuantity(5)
            ->setPurchasePrice(10)
            ->setExpirationDate(new \DateTime());
        
        $inventoryLevel = new InventoryLevel();
        $inventoryLevel->setInventoryItem($inventoryItem);
        $inventoryLevel->setWarehouse($warehouse);
        $inventoryLevel->setInventoryQty(5);
        $inventoryLevel->addInventoryBatch($inventoryBatch);

        return $inventoryLevel;
    }

    /**
     * @param string  $key
     * @param InventoryItem $entity
     */
    public function fillEntityData($key, $entity)
    {
        switch ($key) {
            case 'sku_001':
                return;
        }

        parent::fillEntityData($key, $entity);
    }

    /**
     * Create Product
     *
     * @return Product
     */
    public function createProduct()
    {
        $name = new LocalizedFallbackValue();
        $name->setString('SKU 1');
        
        $entity = new Product();
        $entity->setDefaultName('SKU 1');
        $entity->addName($name);
        $entity->setSku('sku_001');

        return $entity;
    }
}
