<?php

namespace Marello\Bundle\InventoryBundle\ImportExport\TemplateFixture;

use Oro\Bundle\ImportExportBundle\TemplateFixture\AbstractTemplateRepository;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateFixtureInterface;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;

class WarehouseFixture extends AbstractTemplateRepository implements TemplateFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEntityClass()
    {
        return Warehouse::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->getEntityData('default');
    }

    /**
     * {@inheritdoc}
     */
    protected function createEntity($key)
    {
        $warehouse = new Warehouse(null, true);
        $warehouse->setCode('warehouse_13');

        return $warehouse;
    }

    /**
     * @param string  $key
     * @param Warehouse $entity
     */
    public function fillEntityData($key, $entity)
    {
        switch ($key) {
            case 'default':
                return;
        }

        parent::fillEntityData($key, $entity);
    }
}
