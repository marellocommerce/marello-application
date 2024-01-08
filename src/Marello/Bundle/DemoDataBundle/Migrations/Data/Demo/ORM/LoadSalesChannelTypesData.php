<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Marello\Bundle\SalesBundle\Migrations\Data\ORM\LoadSalesChannelTypesData as BaseLoadSalesChannelTypesData;

class LoadSalesChannelTypesData extends BaseLoadSalesChannelTypesData
{
    /** @var array */
    protected $data = [
        'webshop' => 'Webshop',
        'marketplace' => 'Marketplace'
    ];
}
