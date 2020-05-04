<?php

namespace Marello\Bundle\MagentoBundle\Provider\Connector;

class PriceConnector extends AbstractMagentoConnector implements TwoWaySyncConnectorInterface
{
    const TYPE = 'price';
    const IMPORT_JOB = 'mage_price_import';
    const EXPORT_JOB = 'mage_price_export';
    
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
        return 'marello.magento.connector.price.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportEntityFQCN()
    {
        return self::MARELLO_PRODUCT_PRICE;
    }

    /**
     * {@inheritdoc}
     */
    public function getExportEntityFQCN()
    {
        return self::MARELLO_PRODUCT_PRICE;
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
