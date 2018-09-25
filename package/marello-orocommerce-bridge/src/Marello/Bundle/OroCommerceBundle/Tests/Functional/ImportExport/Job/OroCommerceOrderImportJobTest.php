<?php

namespace Marello\Bundle\OroCommerceBundle\Tests\Functional\ImportExport\Job;

use Marello\Bundle\OrderBundle\Entity\Customer;
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

    public function testImportOrdersWithNotExistentProducts()
    {
        $ordersBefore = $this->managerRegistry
            ->getManagerForClass(Order::class)
            ->getRepository(Order::class)
            ->findAll();
        $this->assertEmpty($ordersBefore);

        $customersBefore = $this->managerRegistry
            ->getManagerForClass(Customer::class)
            ->getRepository(Customer::class)
            ->findAll();
        $this->assertEmpty($customersBefore);

        $jobLog = [];

        $this->runImportExportConnectorsJob(
            self::SYNC_PROCESSOR,
            $this->channel,
            OroCommerceOrderConnector::TYPE,
            [],
            $jobLog
        );

        $ordersAfter = $this->managerRegistry
            ->getManagerForClass(Order::class)
            ->getRepository(Order::class)
            ->findAll();
        $this->assertEmpty($ordersAfter);

        $customersAfter = $this->managerRegistry
            ->getManagerForClass(Customer::class)
            ->getRepository(Customer::class)
            ->findAll();
        $this->assertEmpty($customersAfter);
    }
    
    public function testImportOrdersWithExistentProducts()
    {
        $this->loadFixtures([LoadProductData::class]);
        $ordersBefore = $this->managerRegistry
            ->getManagerForClass(Order::class)
            ->getRepository(Order::class)
            ->findAll();
        $this->assertEmpty($ordersBefore);

        $customersBefore = $this->managerRegistry
            ->getManagerForClass(Customer::class)
            ->getRepository(Customer::class)
            ->findAll();
        $this->assertEmpty($customersBefore);

        $jobLog = [];

        $this->runImportExportConnectorsJob(
            self::SYNC_PROCESSOR,
            $this->channel,
            OroCommerceOrderConnector::TYPE,
            [],
            $jobLog
        );

        $ordersAfter = $this->managerRegistry
            ->getManagerForClass(Order::class)
            ->getRepository(Order::class)
            ->findAll();
        $this->assertCount(1, $ordersAfter);
        
        $customersAfter = $this->managerRegistry
            ->getManagerForClass(Customer::class)
            ->getRepository(Customer::class)
            ->findAll();
        $this->assertCount(1, $customersAfter);
    }
}
