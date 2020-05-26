<?php

namespace Marello\Bundle\Magento2Bundle\Migrations\Data\ORM;

use Oro\Bundle\EntityExtendBundle\Migration\Fixture\AbstractEnumFixture;
use Marello\Bundle\Magento2Bundle\Entity\Product;

class LoadProductStatus extends AbstractEnumFixture
{
    /**
     * {@inheritDoc}
     */
    protected function getData()
    {
        return [
            Product::STATUS_READY => 'Ready',
            Product::STATUS_SYNC_ISSUE => 'Sync Issue'
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultValue()
    {
        return Product::STATUS_READY;
    }

    /**
     * {@inheritDoc}
     */
    protected function getEnumCode()
    {
        return 'marello_m2_p_status';
    }
}
