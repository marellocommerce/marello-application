<?php

namespace Marello\Bundle\InventoryBundle\ImportExport\TemplateFixture;

use Oro\Bundle\ImportExportBundle\TemplateFixture\AbstractTemplateRepository;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateFixtureInterface;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;

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
     * @param Account $entity
     */
    public function fillEntityData($key, $entity)
    {
        $productRepo = $this->templateManager
            ->getEntityRepository('Marello\Bundle\ProductBundle\Entity\Product');
        $warehouseRepo = $this->templateManager
            ->getEntityRepository('Marello\Bundle\InventoryBundle\Entity\Warehouse');

        switch ($key) {
            case 'Macbook':
                $entity
                    ->setId(1)
                    ->setProduct($productRepo->getEntity('Macbook Pro'))
                    ->setQuantity(10)
                    ->setWarehouse($warehouseRepo->getEntity('main'));
                return;
        }

        parent::fillEntityData($key, $entity);
    }
}
