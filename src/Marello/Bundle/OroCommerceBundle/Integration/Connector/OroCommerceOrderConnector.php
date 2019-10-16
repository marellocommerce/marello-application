<?php

namespace Marello\Bundle\OroCommerceBundle\Integration\Connector;

use Marello\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\IntegrationBundle\Provider\TwoWaySyncConnectorInterface;

class OroCommerceOrderConnector extends AbstractOroCommerceConnector implements TwoWaySyncConnectorInterface
{
    const TYPE = 'order';
    const IMPORT_JOB = 'orocommerce_order_import';
    const EXPORT_JOB = 'orocommerce_order_export';
    
    /**
     * {@inheritdoc}
     */
    protected function getConnectorSource()
    {
        return $this->transport->getOrders($this->getLastSyncDate());
    }
    
    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'marello.orocommerce.connector.order.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportEntityFQCN()
    {
        return Order::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getImportJobName()
    {
        return self::IMPORT_JOB;
    }

    /**
     * {@inheritdoc}
     */
    public function getExportJobName()
    {
        return self::EXPORT_JOB;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }
}
