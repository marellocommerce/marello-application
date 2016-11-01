<?php

namespace Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures;

use Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadOrderData;
use Marello\Bundle\PricingBundle\Tests\Functional\DataFixtures\LoadProductChannelPricingDataTest;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductDataTest;
use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesDataTest;

class LoadOrderDataTest extends LoadOrderData
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadSalesDataTest::class,
            LoadProductDataTest::class,
            LoadProductChannelPricingDataTest::class,
        ];
    }

    /**
     * @return array|bool
     */
    protected function popOrderRow()
    {
        if (!$this->ordersFile) {
            $this->ordersFile       = fopen(__DIR__ . '/dictionaries/order_data.csv', 'r');
            $this->ordersFileHeader = fgetcsv($this->ordersFile, 1000, ';');
        }

        $row = fgetcsv($this->ordersFile, 1000, ';');

        return $row !== false
            ? array_combine($this->ordersFileHeader, $row)
            : false;
    }

    /**
     * @return array|bool
     */
    protected function popOrderItemRow()
    {
        if (!$this->itemsFile) {
            $this->itemsFile       = fopen(__DIR__ . '/dictionaries/order_items.csv', 'r');
            $this->itemsFileHeader = fgetcsv($this->itemsFile, 1000, ',');
        }

        $row = fgetcsv($this->itemsFile, 1000, ',');

        return $row !== false
            ? array_combine($this->itemsFileHeader, $row)
            : false;
    }
}
