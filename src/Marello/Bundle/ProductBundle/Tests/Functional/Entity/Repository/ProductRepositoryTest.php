<?php

namespace Marello\Bundle\ProductBundle\Tests\Functional\Entity\Repository;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;
use Marello\Bundle\InventoryBundle\Tests\Functional\DataFixtures\LoadInventoryData;

class ProductRepositoryTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], self::generateBasicAuthHeader());
        $this->client->useHashNavigation(true);

        $this->loadFixtures([
            LoadInventoryData::class
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function testPurchaseOrderItemCandidates()
    {
        /** @var ProductRepository $productRepository */
        $productRepository = self::getContainer()
            ->get('doctrine')
            ->getRepository(Product::class);

        $results = $productRepository->getPurchaseOrderItemsCandidates();
        static::assertCount(1, $results);
    }
}
