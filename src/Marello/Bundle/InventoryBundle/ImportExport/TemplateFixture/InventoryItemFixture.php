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
        return 'Marello\Bundle\InventoryBundle\Entity\InventoryItem';
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
        return new InventoryItem();
    }

    /**
     * @param string  $key
     * @param InventoryItem $entity
     */
    public function fillEntityData($key, $entity)
    {
        $warehouseRepo = $this->templateManager
            ->getEntityRepository('Marello\Bundle\InventoryBundle\Entity\Warehouse');

        switch ($key) {
            case 'Macbook':
                $entity
                    ->setId(1)
                    ->setProduct($this->createProduct($entity))
                    ->setQuantity(12)
                    ->setWarehouse($warehouseRepo->getEntity('main'));
                return;
        }

        parent::fillEntityData($key, $entity);
    }

    /**
     * Create Product
     *
     * @param $item
     *
     * @return Product
     */
    public function createProduct($item)
    {
        $entity = new Product();
        $entity->setName('Woood Coffee Table');
        $entity->setSku('WCT-1');
        $entity->setPrice(399.00);

        $status = new ProductStatus('enabled');
        $entity->setStatus($status);

        $channel = new SalesChannel('magento');
        $entity->addChannel($channel);

        $entity->addInventoryItem($item);
        return $entity;
    }
}
