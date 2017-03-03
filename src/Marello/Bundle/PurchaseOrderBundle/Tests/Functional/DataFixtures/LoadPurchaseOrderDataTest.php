<?php

namespace Marello\Bundle\PurchaseOrderBundle\Tests\Functional\DataFixtures;

use Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadPurchaseOrderData;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductDataTest;

class LoadPurchaseOrderDataTest extends LoadPurchaseOrderData
{

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            LoadProductDataTest::class,
        ];
    }
}
