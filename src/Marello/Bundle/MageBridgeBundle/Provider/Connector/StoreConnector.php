<?php

namespace Marello\Bundle\MageBridgeBundle\Provider\Connector;

use Marello\Bundle\MageBridgeBundle\Entity\Store;

use Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface;

class StoreConnector  extends AbstractMagentoConnector implements ConnectorInterface
{
    const TYPE = 'store';

    /**
     * {@inheritdoc}
     */
    protected function getConnectorSource()
    {
        return $this->transport->getStores();
    }


    public function getImportEntityFQCN()
    {
        return Store::class;
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
        return 'magento_store_import';
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }
}
