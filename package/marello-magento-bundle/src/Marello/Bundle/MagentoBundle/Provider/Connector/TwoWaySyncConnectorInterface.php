<?php
namespace Marello\Bundle\MagentoBundle\Provider\Connector;

use \Oro\Bundle\IntegrationBundle\Provider\TwoWaySyncConnectorInterface as BaseTwoWaySyncConnectorInterface;

interface TwoWaySyncConnectorInterface extends BaseTwoWaySyncConnectorInterface
{
    /**
     * {@inheritdoc}
     */
    public function getExportEntityFQCN();
}
