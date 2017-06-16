<?php

namespace Marello\Bundle\InventoryBundle\ImportExport\TemplateFixture;

use Marello\Bundle\InventoryBundle\Entity\StockLevel;
use Oro\Bundle\ImportExportBundle\TemplateFixture\AbstractTemplateRepository;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateFixtureInterface;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\ProductBundle\Entity\Product;

class InventoryItemFixture extends AbstractTemplateRepository implements TemplateFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEntityClass()
    {
        return InventoryItem::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->getEntityData('MARELLO_MUG_OWL');
    }

    /**
     * {@inheritdoc}
     */
    protected function createEntity($key)
    {
        $warehouseRepo = $this->templateManager
            ->getEntityRepository('Marello\Bundle\InventoryBundle\Entity\Warehouse');

        $product = $this->createProduct();
        $inventoryItem = new InventoryItem($warehouseRepo->getEntity('main'), $product);
        $stockLevel = new StockLevel(
            $inventoryItem,
            25,
            0,
            25,
            0,
            'import'
        );

        $inventoryItem->changeCurrentLevel($stockLevel);

        return $inventoryItem;
    }

    /**
     * @param string  $key
     * @param InventoryItem $entity
     */
    public function fillEntityData($key, $entity)
    {
        switch ($key) {
            case 'MARELLO_MUG_OWL':
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
        $entity->setName('Marello Mug');
        $entity->setSku('MARELLO_MUG_OWL');
        $entity->setDesiredStockLevel(0);
        $entity->setPurchaseStockLevel(0);

        return $entity;
    }
}
