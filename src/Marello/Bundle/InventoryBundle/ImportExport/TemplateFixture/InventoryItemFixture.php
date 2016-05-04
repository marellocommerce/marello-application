<?php

namespace Marello\Bundle\InventoryBundle\ImportExport\TemplateFixture;

use Oro\Bundle\ImportExportBundle\TemplateFixture\AbstractTemplateRepository;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateFixtureInterface;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\ProductStatus;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;

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
        return $this->getEntityData('Macbook');
    }

    /**
     * {@inheritdoc}
     */
    protected function createEntity($key)
    {
        $warehouseRepo = $this->templateManager
            ->getEntityRepository('Marello\Bundle\InventoryBundle\Entity\Warehouse');

        $inventoryItem = new InventoryItem($warehouseRepo->getEntity('main'), $this->createProduct());
        $inventoryItem->setStockLevels('import', 25);
    }

    /**
     * @param string  $key
     * @param InventoryItem $entity
     */
    public function fillEntityData($key, $entity)
    {
        switch ($key) {
            case 'Macbook':
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
        $entity->setName('Woood Coffee Table');
        $entity->setSku('WCT-1');
        $entity->setPrice(399.00);

        $status = new ProductStatus('enabled');
        $entity->setStatus($status);

        $channel = new SalesChannel('magento');
        $entity->addChannel($channel);

        return $entity;
    }
}
