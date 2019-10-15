<?php

namespace Marello\Bundle\OroCommerceBundle\Integration\Connector;

use Marello\Bundle\InventoryBundle\Entity\VirtualInventoryLevel;
use Oro\Bundle\IntegrationBundle\Provider\TwoWaySyncConnectorInterface;

class OroCommerceInventoryLevelConnector extends AbstractOroCommerceConnector implements TwoWaySyncConnectorInterface
{
    const TYPE = 'inventory_level';
    const IMPORT_JOB = 'orocommerce_inventorylevel_import';
    const EXPORT_JOB = 'orocommerce_inventorylevel_export';
    
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
        return 'marello.orocommerce.connector.inventory_level.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportEntityFQCN()
    {
        return VirtualInventoryLevel::class;
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
