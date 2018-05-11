<?php

namespace Marello\Bundle\MagentoBundle\Provider\Connector;

use Oro\Bundle\ImportExportBundle\Reader\IteratorBasedReader;
use Oro\Bundle\IntegrationBundle\Provider\TwoWaySyncConnectorInterface;

class ProductConnector extends AbstractMagentoConnector implements TwoWaySyncConnectorInterface
{
    const IMPORT_JOB_NAME = 'mage_product_import';

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
        return self::PRODUCT_TYPE;
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
        return 'magento_product_export';
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'product';
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
