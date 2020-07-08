<?php

namespace Marello\Bundle\Magento2Bundle\Integration\Connector;

use Marello\Bundle\Magento2Bundle\Entity\ProductTaxClass;
use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;

class ProductTaxClassConnector extends AbstractConnector implements DictionaryConnectorInterface
{
    /** @var string */
    public const TYPE = 'product_tax_class_dictionary';

    /**
     * {@inheritDoc}
     */
    protected function getConnectorSource()
    {
        return $this->transport->getProductTaxClasses();
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return 'marello.magento2.connector.product_tax_class.label';
    }

    /**
     * {@inheritDoc}
     */
    public function getImportEntityFQCN()
    {
        return ProductTaxClass::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getImportJobName()
    {
        return 'marello_magento2_product_tax_class_import';
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return self::TYPE;
    }
}
