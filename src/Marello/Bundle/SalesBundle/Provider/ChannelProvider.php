<?php

namespace Marello\Bundle\SalesBundle\Provider;

use Doctrine\Common\Persistence\ObjectManager;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;

class ChannelProvider
{
    /** @var ObjectManager $manager */
    protected $manager;

    /**
     * ChannelProvider constructor.
     * @param ObjectManager $manager
     */
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Returns ids of all related sales channels for a product.
     *
     * @param Product $product
     *
     * @return array $ids
     */
    public function getSalesChannelsIds(Product $product)
    {
        $ids = [];
        $product
            ->getChannels()
            ->map(function (SalesChannel $channel) use (&$ids) {
                $ids[] = $channel->getId();
            });

        return $ids;
    }

    /**
     * Returns ids of all sales channels which are not in related to a product.
     *
     * @param Product $product
     *
     * @return array $ids
     */
    public function getExcludedSalesChannelsIds(Product $product)
    {
        $relatedIds = $this->getSalesChannelsIds($product);
        $excludedIds = [];

        $ids = $this->manager
            ->getRepository(SalesChannel::class)
            ->findExcludedSalesChannelIds($relatedIds);

        foreach ($ids as $k => $v) {
            $excludedIds[] = $v['id'];
        }

        return $excludedIds;
    }
}
