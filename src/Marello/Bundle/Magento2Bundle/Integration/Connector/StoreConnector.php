<?php

namespace Marello\Bundle\Magento2Bundle\Integration\Connector;

use Marello\Bundle\Magento2Bundle\Entity\Store;

class StoreConnector extends AbstractConnector implements DictionaryConnectorInterface
{
    /** @var string */
    public const TYPE = 'store_dictionary';
    public const IMPORT_JOB_NAME = 'marello_magento2_store_rest_import';

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
        return self::TYPE;
    }

    /**
     * {@inheritDoc}
     */
    public function getImportJobName()
    {
        return self::IMPORT_JOB_NAME;
    }
}
