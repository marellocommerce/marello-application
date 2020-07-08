<?php

namespace Marello\Bundle\Magento2Bundle\Integration\Connector;

use Marello\Bundle\Magento2Bundle\Entity\AttributeSet;

class AttributeSetConnector extends AbstractMagento2Connector
{
    /**
     * {@inheritDoc}
     */
    protected function getConnectorSource()
    {
        return $this->transport->getAttributeSets();
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return 'marello.magento2.connector.attributeset.label';
    }

    /**
     * {@inheritDoc}
     */
    public function getImportEntityFQCN()
    {
        return AttributeSet::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return 'magento2_attributeset';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportJobName()
    {
        return 'marello_magento2_attributeset_rest_import';
    }
}
