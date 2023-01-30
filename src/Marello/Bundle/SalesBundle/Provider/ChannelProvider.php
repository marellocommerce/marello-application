<?php

namespace Marello\Bundle\SalesBundle\Provider;

use Doctrine\Persistence\ObjectManager;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class ChannelProvider
{
    public function __construct(
        protected ObjectManager $manager,
        protected AclHelper $aclHelper
    ) {
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
            ->findExcludedSalesChannelIds($relatedIds, $this->aclHelper);

        foreach ($ids as $k => $v) {
            $excludedIds[] = $v['id'];
        }

        return $excludedIds;
    }
}
