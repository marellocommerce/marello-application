<?php

namespace Marello\Bundle\PricingBundle\Tests\Functional\DataFixtures;

use Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductChannelPricingData;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductDataTest;
use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesDataTest;

class LoadProductChannelPricingDataTest extends LoadProductChannelPricingData
{
    /**
     * @return array
     */
    public function getDependencies()
    {
        return [
            LoadSalesDataTest::class,
            LoadProductDataTest::class,
        ];
    }

    /**
     * Get dictionary file by name
     *
     * @param $name
     *
     * @return string
     */
    protected function getDictionary($name)
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'dictionaries' . DIRECTORY_SEPARATOR . $name;
    }
}
