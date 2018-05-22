<?php
/**
 * Created by PhpStorm.
 * User: muhsin
 * Date: 18/05/2018
 * Time: 10:33
 */

namespace Marello\Bundle\MagentoBundle\Provider\Connector;

use \Oro\Bundle\IntegrationBundle\Provider\TwoWaySyncConnectorInterface as BaseTwoWaySyncConnectorInterface;

interface TwoWaySyncConnectorInterface extends BaseTwoWaySyncConnectorInterface
{
    /**
     * {@inheritdoc}
     */
    public function getExportEntityFQCN();
}
