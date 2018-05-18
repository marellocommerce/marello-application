<?php

namespace Marello\Bundle\MagentoBundle\Provider\Connector;

//use Oro\Bundle\IntegrationBundle\Provider\TwoWaySyncConnectorInterface;

class ProductConnector extends AbstractMagentoConnector implements TwoWaySyncConnectorInterface
{
    const IMPORT_JOB_NAME = 'mage_product_import';
    const EXPORT_JOB_NAME = 'mage_product_export';
    const TYPE            = 'product';

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'marello.magento.connector.product.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportEntityFQCN()
    {
        return self::MAGENTO_PRODUCT_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getExportEntityFQCN()
    {
        return self::MARELLO_PRODUCT_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getImportJobName()
    {
        return self::IMPORT_JOB_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getExportJobName()
    {
        return self::EXPORT_JOB_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConnectorSource()
    {
        return $this->transport->getProducts();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsForceSync()
    {
        return true;
    }
}
