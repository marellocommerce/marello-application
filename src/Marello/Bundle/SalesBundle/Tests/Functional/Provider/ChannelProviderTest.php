<?php

namespace Marello\Bundle\SalesBundle\Tests\Provider;

use Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductData;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Provider\ChannelProvider;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class ChannelProviderTest extends WebTestCase
{

    public function setUp()
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader()
        );

        $this->loadFixtures([
            LoadProductData::class
        ]);
    }

    public function testGetAssociatedSalesChannelIds()
    {
        /** @var ChannelProvider $provider */
        $provider = $this->getMockBuilder(ChannelProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Product $product */
        $product = $this->getReference('marello-product-0');

        $ids = [];
        $product
            ->getChannels()
            ->map(function (SalesChannel $channel) use (&$ids) {
                $ids[] = $channel->getId();
            });

        $actual = $provider->getSalesChannelsIds($product);

        $this->assertCount($ids, $actual);
        $this->assertEquals($ids, $actual);
    }
}
