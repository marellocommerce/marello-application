<?php

namespace Marello\Bundle\Magento2Bundle\Async;

final class Topics
{
    public const SALES_CHANNEL_REMOVED = 'marello.magento2.sales_channel_removed';
    public const SALES_CHANNEL_STATE_CHANGED = 'marello.magento2.sales_channel_state_changed';
    public const REMOVE_REMOTE_DATA_FOR_DISABLED_INTEGRATION =
        'marello.magento2.remove_remote_data_for_disabled_integration';
    public const REMOVE_REMOTE_PRODUCT_FOR_DISABLED_INTEGRATION =
        'marello.magento2.remove_remote_product_for_disabled_integration';
}
