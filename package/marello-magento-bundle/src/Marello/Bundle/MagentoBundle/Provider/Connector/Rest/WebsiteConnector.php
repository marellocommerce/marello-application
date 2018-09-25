<?php

namespace Marello\Bundle\MagentoBundle\Provider\Connector\Rest;

use Marello\Bundle\MagentoBundle\Provider\Connector\WebsiteConnector as BaseConnector;

class WebsiteConnector extends BaseConnector
{
    /**
     * {@inheritdoc}
     */
    public function getImportJobName()
    {
        return 'mage_website_rest_import';
    }
}
