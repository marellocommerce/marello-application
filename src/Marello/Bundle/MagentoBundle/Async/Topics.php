<?php
namespace Marello\Bundle\MagentoBundle\Async;

class Topics
{
    const SYNC_INITIAL_INTEGRATION          = 'marello.magento.sync_initial_integration';
    const SYNC_PRODUCT_ENTITY_INTEGRATION   = 'marello.magento.sync_product_entity_integration';

    const SYNC_CREATE_ACTION                = 'create';
    const SYNC_UPDATE_ACTION                = 'update';
    const SYNC_REMOVE_ACTION                = 'remove';
}
