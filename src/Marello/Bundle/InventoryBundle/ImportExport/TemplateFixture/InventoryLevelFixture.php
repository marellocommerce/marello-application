<?php

namespace Marello\Bundle\InventoryBundle\ImportExport\TemplateFixture;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ImportExportBundle\TemplateFixture\AbstractTemplateRepository;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateFixtureInterface;

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

        $inventoryLevel = new InventoryLevel();
        $inventoryLevel->setInventoryItem($inventoryItem);
        $inventoryLevel->setWarehouse($warehouse);
        $inventoryLevel->setInventoryQty(5);

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
        $entity = new Product();
        $entity->setName('SKU 1');
        $entity->setSku('sku_001');

        return $entity;
    }
}
