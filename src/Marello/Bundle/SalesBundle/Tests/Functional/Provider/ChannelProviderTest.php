<?php

namespace Marello\Bundle\SalesBundle\Tests\Provider;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Provider\ChannelProvider;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;

class ChannelProviderTest extends WebTestCase
{
    /**
     * @var EntityManager
     */
    protected $em;

    public function setUp()
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader()
        );

        $this->loadFixtures(
            [
                LoadProductData::class,
            ]
        );

        $this->em = $this->client->getKernel()->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * test get sales channels ids
     */
    public function testGetAssociatedSalesChannelIds()
    {
        /** @var ChannelProvider $provider */
        $provider = new ChannelProvider($this->em);
        /** @var Product $product */
        $product = $this->getReference(LoadProductData::PRODUCT_1_REF);

        $actual = $provider->getSalesChannelsIds($product);

        $this->assertCount($product->getChannels()->count(), $actual);

        $ids = [];
        $product
            ->getChannels()
            ->map(
                function (SalesChannel $channel) use (&$ids) {
                    $ids[] = $channel->getId();
                }
            );
        $this->assertEquals($ids, $actual);
    }

    /**
     * test get excluded sales channels ids
     */
    public function testGetNotAssociatedSalesChannelIds()
    {
        /** @var Product $product */
        $product = $this->getReference(LoadProductData::PRODUCT_1_REF);

        $provider = new ChannelProvider($this->em);

        $ids = [];
        $product
            ->getChannels()
            ->map(
                function (SalesChannel $channel) use (&$ids) {
                    $ids[] = $channel->getId();
                }
            );

        $actual = $provider->getExcludedSalesChannelsIds($product);
        $this->assertNotEquals($ids, $actual);
    }
}
