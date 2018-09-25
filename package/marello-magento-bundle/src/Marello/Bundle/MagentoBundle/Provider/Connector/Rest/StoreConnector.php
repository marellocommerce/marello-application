<?php

namespace Marello\Bundle\MagentoBundle\Provider\Connector\Rest;

use Marello\Bundle\MagentoBundle\Provider\Connector\StoreConnector as BaseConnector;

class StoreConnector extends BaseConnector
{
    /**
     * {@inheritdoc}
     */
    public function getImportJobName()
    {
        return 'mage_store_rest_import';
    }
}
