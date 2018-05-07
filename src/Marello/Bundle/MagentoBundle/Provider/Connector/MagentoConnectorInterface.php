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
    const STORE_TYPE   = 'Marello\\Bundle\\MagentoBundle\\Entity\\Store';
    const WEBSITE_TYPE = 'Marello\\Bundle\\MagentoBundle\\Entity\\Website';

//    const ORDER_TYPE                    = 'Oro\\Bundle\\MagentoBundle\\Entity\\Order';
//    const CREDIT_MEMO_TYPE              = 'Oro\\Bundle\\MagentoBundle\\Entity\\CreditMemo';
//    const ORDER_ADDRESS_TYPE            = 'Oro\\Bundle\\MagentoBundle\\Entity\\OrderAddress';
//    const ORDER_ADDRESS_COLLECTION_TYPE = 'ArrayCollection<Oro\\Bundle\\MagentoBundle\\Entity\\OrderAddress>';
//    const ORDER_ITEM_TYPE               = 'Oro\\Bundle\\MagentoBundle\\Entity\\OrderItem';
//    const ORDER_ITEM_COLLECTION_TYPE    = 'ArrayCollection<Oro\\Bundle\\MagentoBundle\\Entity\\OrderItem>';
//
//    const REGION_TYPE = 'Oro\\Bundle\\MagentoBundle\\Entity\\Region';
}
