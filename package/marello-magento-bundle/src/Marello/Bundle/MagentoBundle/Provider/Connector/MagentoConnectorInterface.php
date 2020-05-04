<?php

namespace Marello\Bundle\MagentoBundle\Provider\Connector;

use Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface;

/**
 * Interface MagentoConnectorInterface
 *
 * @package Marello\Bundle\MagentoBundle\Provider
 * This interface should be implemented by magento related connectors
 * Contains just general constants
 */
interface MagentoConnectorInterface extends ConnectorInterface
{
    const STORE_TYPE                      = 'Marello\\Bundle\\MagentoBundle\\Entity\\Store';
    const WEBSITE_TYPE                    = 'Marello\\Bundle\\MagentoBundle\\Entity\\Website';

    const MAGENTO_PRODUCT_TYPE            = 'Marello\\Bundle\\MagentoBundle\\Entity\\Product';
    const MARELLO_PRODUCT_TYPE            = 'Marello\\Bundle\\ProductBundle\\Entity\\Product';

    const MAGENTO_CATEGORY_TYPE           = 'Marello\\Bundle\\MagentoBundle\\Entity\\Category';
    const MARELLO_CATEGORY_TYPE           = 'Marello\\Bundle\\CatalogBundle\\Entity\\Category';

    const MARELLO_VIRTUAL_INVENTORY       = 'Marello\\Bundle\InventoryBundle\\Entity\\VirtualInventoryLevel';
    const MARELLO_PRODUCT_PRICE           = 'Marello\\Bundle\PricingBundle\\Entity\\ProductPrice';

    const MARELLO_ORDER_TYPE              = 'Marello\\Bundle\OrderBundle\\Entity\\Order';
}
