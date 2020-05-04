<?php

namespace Marello\Bundle\MagentoBundle\Provider\Connector;

class StoreConnector extends AbstractMagentoConnector implements DictionaryConnectorInterface
{
    const IMPORT_JOB_NAME = 'mage_store_import';
    const TYPE = 'store_dictionary';

    /**
     * {@inheritdoc}
     */
    protected function getConnectorSource()
    {
        return $this->transport->getStores();
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'marello.magento.connector.store.label';
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
    public function getType()
    {
        return self::TYPE;
    }
}
