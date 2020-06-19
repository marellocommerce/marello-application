<?php

namespace Marello\Bundle\Magento2Bundle\Integration\Connector;

use Marello\Bundle\Magento2Bundle\Entity\Website;

class WebsiteConnector extends AbstractMagento2Connector implements DictionaryConnectorInterface
{
    /**
     * {@inheritDoc}
     */
    protected function getConnectorSource()
    {
        return $this->transport->getWebsites();
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return 'marello.magento2.connector.website.label';
    }

    /**
     * {@inheritDoc}
     */
    public function getImportEntityFQCN()
    {
        return Website::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return 'website_dictionary';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportJobName()
    {
        return 'marello_magento2_website_rest_import';
    }
}
