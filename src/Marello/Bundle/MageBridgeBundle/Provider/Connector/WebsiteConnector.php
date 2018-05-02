<?php

namespace Marello\Bundle\MageBridgeBundle\Provider\Connector;

use Marello\Bundle\MageBridgeBundle\Entity\Website;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface;

class WebsiteConnector extends AbstractMagentoConnector implements ConnectorInterface
{
    const TYPE = 'website';

    /**
     * {@inheritdoc}
     */
    protected function getConnectorSource()
    {
        return $this->transport->getWebsites();
    }

    public function getImportEntityFQCN()
    {
        return Website::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'oro.magento.connector.website.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportJobName()
    {
        return 'magento_website_import';
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }
}
