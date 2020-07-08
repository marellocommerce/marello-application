<?php

namespace Marello\Bundle\Magento2Bundle\Integration\Connector;

use Marello\Bundle\Magento2Bundle\Entity\Order;
use Marello\Bundle\Magento2Bundle\Exception\RuntimeException;
use Marello\Bundle\Magento2Bundle\ImportExport\Converter\OrderDataConverter;
use Marello\Bundle\Magento2Bundle\Transport\Rest\Iterator\OrderIterator;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Provider\AllowedConnectorInterface;
use Oro\Bundle\IntegrationBundle\Provider\TwoWaySyncConnectorInterface;

class OrderConnector extends AbstractImportConnector implements
    AllowedConnectorInterface,
    TwoWaySyncConnectorInterface,
    SingleWebsiteConnectorInterface
{
    public const TYPE = 'order';

    /**
     * @return OrderIterator
     * @throws RuntimeException
     */
    protected function getConnectorSource()
    {
        return $this->transport->getOrders();
    }

    public function getLabel()
    {
        // TODO: Implement getLabel() method.
    }

    public function getImportEntityFQCN()
    {
        return Order::class;
    }

    public function getImportJobName()
    {
        // TODO: Implement getImportJobName() method.
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
        return OrderDataConverter::UPDATED_AT_COLUMN_NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function getUpdateAtColumnFormat(): string
    {
        return OrderDataConverter::UPDATED_AT_COLUMN_FORMAT;
    }

    /**
     * @todo Remove this after connector was fully implemented
     *
     * @param Channel $integration
     * @param array $processedConnectorsStatuses
     * @return bool
     */
    public function isAllowed(Channel $integration, array $processedConnectorsStatuses)
    {
        return false;
    }

    public function getExportJobName()
    {
        // TODO: Implement getExportJobName() method.
    }
}
