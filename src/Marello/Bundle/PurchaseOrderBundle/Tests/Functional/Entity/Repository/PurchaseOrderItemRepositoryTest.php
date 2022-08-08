<?php

namespace Marello\Bundle\PurchaseOrderBundle\Tests\Functional\Entity\Repository;

use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;
use Marello\Bundle\PurchaseOrderBundle\Entity\Repository\PurchaseOrderItemRepository;
use Marello\Bundle\PurchaseOrderBundle\Tests\Functional\DataFixtures\LoadPurchaseOrderData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolationPerTest
 */
class PurchaseOrderItemRepositoryTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadPurchaseOrderData::class]);
    }

    private function getRepository(): PurchaseOrderItemRepository
    {
        return self::getContainer()->get('doctrine')->getRepository(PurchaseOrderItem::class);
    }

    public function testGetNotCompletedItemsByProduct()
    {
        $product = $this->getReference(LoadProductData::PRODUCT_1_REF);
        $result = $this->getRepository()->getNotCompletedItemsByProduct($product);

        $this->assertCount(1, $result);
        $this->assertEquals($product, $result[0]->getProduct());
    }
}
