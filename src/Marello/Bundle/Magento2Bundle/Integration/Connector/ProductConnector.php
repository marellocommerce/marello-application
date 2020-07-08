<?php

namespace Marello\Bundle\Magento2Bundle\Integration\Connector;

use Marello\Bundle\Magento2Bundle\Exception\RuntimeException;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Provider\AllowedConnectorInterface;
use Oro\Bundle\IntegrationBundle\Provider\TwoWaySyncConnectorInterface;

class ProductConnector extends AbstractConnector implements
    AllowedConnectorInterface,
    TwoWaySyncConnectorInterface
{
    public const TYPE = 'product';
    public const EXPORT_JOB = 'marello_magento2_product_rest_export';

    public const EXPORT_ACTION_CREATE = 'create';
    public const EXPORT_ACTION_UPDATE = 'update';
    public const EXPORT_ACTION_UPDATE_WEBSITE_SCOPE_DATA = 'updatedWebsiteScopeData';
    public const EXPORT_ACTION_DELETE = 'delete';
    public const EXPORT_ACTION_DELETE_ON_CHANNEL = 'deleteOnChannel';

    /**
     * {@inheritDoc}
     */
    protected function getConnectorSource()
    {
        return new \ArrayIterator();
    }

    /**
     * Do not allow to run import sync, because this connector only use in reverse sync
     *
     * {@inheritDoc}
     */
    public function isAllowed(Channel $integration, array $processedConnectorsStatuses)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return 'marello.magento2.connector.product.label';
    }

    /**
     * {@inheritDoc}
     */
    public function getImportEntityFQCN()
    {
        return Product::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getImportJobName()
    {
        throw new RuntimeException('[Magento 2] ProductConnector is not support import!');
    }

    /**
     * {@inheritDoc}
     */
    public function getExportJobName()
    {
        return self::EXPORT_JOB;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return self::TYPE;
    }
}
