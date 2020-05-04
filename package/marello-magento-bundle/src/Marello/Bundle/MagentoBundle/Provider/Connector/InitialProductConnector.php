<?php

namespace Marello\Bundle\MagentoBundle\Provider\Connector;

class InitialProductConnector extends AbstractMagentoConnector implements InitialConnectorInterface
{
    const TYPE = 'product_initial';

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'marello.magento.connector.product_initial.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportJobName()
    {
        return ProductConnector::IMPORT_JOB_NAME;
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
