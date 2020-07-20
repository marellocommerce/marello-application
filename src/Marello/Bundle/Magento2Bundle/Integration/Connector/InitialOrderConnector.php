<?php

namespace Marello\Bundle\Magento2Bundle\Integration\Connector;

use Marello\Bundle\Magento2Bundle\Entity\Order;
use Marello\Bundle\Magento2Bundle\Transport\Rest\Iterator\OrderIterator;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Provider\AllowedConnectorInterface;

class InitialOrderConnector extends AbstractInitialImportConnector implements
    SingleWebsiteConnectorInterface,
    InitialConnectorInterface
{
    public const TYPE = 'order_initial';
    public const IMPORT_JOB_NAME = 'marello_magento2_order_rest_import_initial';

    /**
     * @return OrderIterator
     */
    protected function getConnectorSource()
    {
        return $this->transport->getOrders();
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return 'marello.magento2.connector.initial.order.label';
    }

    /**
     * {@inheritDoc}
     */
    public function getImportEntityFQCN()
    {
        return Order::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getImportJobName()
    {
        return self::IMPORT_JOB_NAME;
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
    public function supportsForceSync()
    {
        return true;
    }
}
