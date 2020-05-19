<?php

namespace Marello\Bundle\Magento2Bundle\Connector;

use Marello\Bundle\Magento2Bundle\Entity\Store;
use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;

class StoreConnector extends AbstractConnector implements DictionaryConnectorInterface
{
    /**
     * {@inheritDoc}
     */
    protected function getConnectorSource()
    {
        return $this->transport->getStores();
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return 'marello.magento2.connector.store.label';
    }

    /**
     * {@inheritDoc}
     */
    public function getImportEntityFQCN()
    {
        return Store::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return 'store_dictionary';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportJobName()
    {
        return 'marello_magento2_store_rest_import';
    }
}
