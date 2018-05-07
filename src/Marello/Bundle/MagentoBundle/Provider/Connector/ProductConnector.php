<?php

namespace Marello\Bundle\MagentoBundle\Provider\Connector;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;

class ProductConnector extends AbstractMagentoConnector implements DictionaryConnectorInterface
{
    const TYPE = 'product';

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
    public function getLabel()
    {
        return 'oro.magento.connector.product.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportJobName()
    {
        return 'mage_product_import';
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
    public function getImportEntityFQCN()
    {
        return self::PRODUCT_TYPE;
    }


    /**
     * @param ContextInterface $context
     */
    protected function initializeTransport(ContextInterface $context)
    {
//        $this->contextMediator->resetInitializedTransport();

        parent::initializeTransport($context);
    }
}
