<?php

namespace Marello\Bundle\MagentoBundle\Provider\Connector;

class InventoryLevelConnector extends AbstractMagentoConnector implements TwoWaySyncConnectorInterface
{
    const TYPE = 'inventory_level';
    const IMPORT_JOB = 'mage_inventorylevel_import';
    const EXPORT_JOB = 'mage_inventorylevel_export';
    
    /**
     * {@inheritdoc}
     */
    protected function getConnectorSource()
    {
        $obj = new \ArrayObject([]);

        return $obj->getIterator();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'marello.magento.connector.inventory_level.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportEntityFQCN()
    {
        return self::MARELLO_VIRTUAL_INVENTORY;
    }

    /**
     * {@inheritdoc}
     */
    public function getExportEntityFQCN()
    {
        return self::MARELLO_VIRTUAL_INVENTORY;
    }

    /**
     * {@inheritdoc}
     */
    public function getImportJobName()
    {
        return self::IMPORT_JOB;
    }

    /**
     * {@inheritdoc}
     */
    public function getExportJobName()
    {
        return self::EXPORT_JOB;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }
}
