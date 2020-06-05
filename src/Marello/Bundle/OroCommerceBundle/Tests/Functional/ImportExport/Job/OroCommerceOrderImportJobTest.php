<?php

namespace Marello\Bundle\OroCommerceBundle\Tests\Functional\ImportExport\Job;

use Marello\Bundle\CustomerBundle\Entity\Customer;
use Marello\Bundle\OroCommerceBundle\Tests\Functional\DataFixtures\LoadAdditionalSalesData;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OroCommerceBundle\Integration\Connector\OroCommerceOrderConnector;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;

class OroCommerceOrderImportJobTest extends AbstractOroCommerceJobTest
{
    /** {@inheritdoc} */
    protected function setUp()
    {
        parent::setUp();

        $orderResponseFile = file_get_contents(__DIR__ . '/../../DataFixtures/data/getOrdersResponse.json');
        $this->restClient
            ->expects(static::at(0))
            ->method('getJSON')
            ->willReturn(json_decode($orderResponseFile, true));

        $orderTaxValuesResponseFile =
            file_get_contents(__DIR__ . '/../../DataFixtures/data/getOrderTaxValuesResponse.json');
        $this->restClient
            ->expects(static::at(1))
            ->method('getJSON')
            ->willReturn(json_decode($orderTaxValuesResponseFile, true));

        $paymentStatusResponseFile =
            file_get_contents(__DIR__ . '/../../DataFixtures/data/getPaymentStatusesResponse.json');
        $this->restClient
            ->expects(static::at(2))
            ->method('getJSON')
            ->willReturn(json_decode($paymentStatusResponseFile, true));

        $orderLineItemsTaxValuesResponseFile =
            file_get_contents(__DIR__ . '/../../DataFixtures/data/getOrderLineItemsTaxValuesResponse.json');
        $this->restClient
            ->expects(static::at(3))
            ->method('getJSON')
            ->willReturn(json_decode($orderLineItemsTaxValuesResponseFile, true));
    }

    public function testImportOrdersWithExistentProducts()
    {
        $ordersBefore = $this->findAllEntities(Order::class);
        $this->assertEmpty($ordersBefore);

        $customersBefore = $this->findAllEntities(Customer::class);
        $this->assertEmpty($customersBefore);

        $jobLog = [];

        $this->runImportExportConnectorsJob(
            self::SYNC_PROCESSOR,
            $this->channel,
            OroCommerceOrderConnector::TYPE,
            [],
            $jobLog
        );

        $ordersAfter = $this->findAllEntities(Order::class);
        $this->assertCount(1, $ordersAfter);

        $customersAfter = $this->findAllEntities(Customer::class);
        $this->assertCount(1, $customersAfter);
    }

    public function testImportOrdersWithNotExistentProducts()
    {
        $this->removeProductsFromSalesChannels();
        $ordersBefore = $this->findAllEntities(Order::class);
        $this->assertCount(1, $ordersBefore);

        $customersBefore = $this->findAllEntities(Customer::class);
        $this->assertCount(1, $customersBefore);

        $jobLog = [];

        $this->runImportExportConnectorsJob(
            self::SYNC_PROCESSOR,
            $this->channel,
            OroCommerceOrderConnector::TYPE,
            [],
            $jobLog
        );

        $ordersAfter = $this->findAllEntities(Order::class);
        // should be the same count as before :)
        $this->assertCount(count($ordersBefore), $ordersAfter);

        $customersAfter = $this->findAllEntities(Customer::class);
        $this->assertCount(count($customersBefore), $customersAfter);
    }

    /**
     * Get all the entities for a certain entity
     * @param $entityClassName
     * @return \object[]
     */
    private function findAllEntities($entityClassName)
    {
        return $this->managerRegistry
            ->getManagerForClass($entityClassName)
            ->getRepository($entityClassName)
            ->findAll();
    }

    /**
     * Remove products from the orocommerce saleschannel in order to test that non existing products
     * will not be imported
     */
    private function removeProductsFromSalesChannels()
    {
        $products = [
            $this->getReference(LoadProductData::PRODUCT_1_REF),
            $this->getReference(LoadProductData::PRODUCT_2_REF),
            $this->getReference(LoadProductData::PRODUCT_3_REF)
        ];
        $manager = $this->managerRegistry
            ->getManagerForClass(Product::class);
        /** @var Product $product */
        foreach ($products as $product) {
            $product->removeChannel($this->getReference(LoadAdditionalSalesData::TEST_SALESCHANNEL_OROCOMMERCE));
            $manager->persist($product);
            $manager->flush();
        }
    }
}
