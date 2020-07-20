<?php

namespace Marello\Bundle\Magento2Bundle\Integration\Connector;

use Marello\Bundle\Magento2Bundle\Entity\Order;
use Marello\Bundle\Magento2Bundle\ImportExport\Converter\MagentoOrderDataConverter;
use Marello\Bundle\Magento2Bundle\Transport\Rest\Iterator\OrderIterator;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Provider\AllowedConnectorInterface;
use Oro\Bundle\IntegrationBundle\Provider\TwoWaySyncConnectorInterface;

class OrderConnector extends AbstractImportConnector implements
    TwoWaySyncConnectorInterface,
    SingleWebsiteConnectorInterface
{
    public const TYPE = 'order';
    public const IMPORT_JOB_NAME = 'marello_magento2_order_rest_import';

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
        return 'marello.magento2.connector.order.label';
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
    public function getUpdateAtColumnName(): string
    {
        return MagentoOrderDataConverter::UPDATED_AT_COLUMN_NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function getUpdateAtColumnFormat(): string
    {
        return MagentoOrderDataConverter::UPDATED_AT_COLUMN_FORMAT;
    }

    /**
     * {@inheritDoc}
     */
    public function getExportJobName()
    {
        // TODO: Implement getExportJobName() method.
    }

    /**
     * {@inheritDoc}
     */
    public function supportsForceSync()
    {
        return true;
    }
}
