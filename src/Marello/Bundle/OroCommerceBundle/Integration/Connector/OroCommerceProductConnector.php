<?php

namespace Marello\Bundle\OroCommerceBundle\Integration\Connector;

use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\IntegrationBundle\Provider\TwoWaySyncConnectorInterface;

class OroCommerceProductConnector extends AbstractOroCommerceConnector implements TwoWaySyncConnectorInterface
{
    const TYPE = 'product';
    const IMPORT_JOB = 'orocommerce_product_import';
    const EXPORT_JOB = 'orocommerce_product_export';
    
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
        return 'marello.orocommerce.connector.product.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportEntityFQCN()
    {
        return Product::class;
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
