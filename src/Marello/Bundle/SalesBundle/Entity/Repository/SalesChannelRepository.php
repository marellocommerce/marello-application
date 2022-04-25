<?php

namespace Marello\Bundle\SalesBundle\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Marello\Bundle\PricingBundle\Entity\ProductChannelPrice;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class SalesChannelRepository extends ServiceEntityRepository
{
    /**
     * @param string $searchTerm
     * @param int $groupId
     * @param array $skippedSalesChannelIds
     * @return QueryBuilder
     */
    public function getActiveSalesChannelBySearchTermLimitedWithGroupIdQB(
        string $searchTerm,
        int $groupId,
        array $skippedSalesChannelIds = []
    ): QueryBuilder {
        $qb = $this->getActiveChannelsQuery();
        $qb
            ->andWhere($qb->expr()->like('LOWER(sc.name)', ':searchTerm'))
            ->andWhere($qb->expr()->eq('sc.group', ':salesChannelGroupId'))
            ->setParameter('searchTerm', '%' . mb_strtolower($searchTerm) . '%')
            ->setParameter('salesChannelGroupId', $groupId)
            ->orderBy('sc.name', 'ASC');

        if (!empty($skippedSalesChannelIds)) {
            $qb
                ->andWhere($qb->expr()->notIn('sc.id', ':skippedSalesChannelIds'))
                ->setParameter('skippedSalesChannelIds', $skippedSalesChannelIds);
        }

        return $qb;
    }

    /**
     * Return product prices for specified channel and productId
     *
     * @param int $salesChannel
     * @param int $productId
     * @param AclHelper $aclHelper
     *
     * @return ProductChannelPrice[]
     */
    public function findOneBySalesChannel($salesChannel, $productId, AclHelper $aclHelper)
    {
        $qb = $this->createQueryBuilder('price');
        $qb
            ->where(
                $qb->expr()->eq('IDENTITY(price.channel)', ':salesChannel'),
                $qb->expr()->eq('IDENTITY(price.product)', ':productId')
            )
            ->setParameter('salesChannel', $salesChannel)
            ->setParameter('productId', $productId);

        return $aclHelper->apply($qb)->getScalarResult();
    }

    /**
     * Get excluded sales channel ids
     * @param array $relatedChannelIds
     * @param AclHelper $aclHelper
     * @return array
     */
    public function findExcludedSalesChannelIds(array $relatedChannelIds, AclHelper $aclHelper)
    {
        $qb = $this->createQueryBuilder('sc')
            ->select('sc.id')
            ->where('sc.id NOT IN(:channels)')
            ->setParameter('channels', $relatedChannelIds);

        return $aclHelper->apply($qb)->getArrayResult();
    }

    /**
     * @return QueryBuilder
     */
    private function getActiveChannelsQuery()
    {
        $qb = $this->createQueryBuilder('sc');
        
        return $qb
            ->where($qb->expr()->eq('sc.active', $qb->expr()->literal(true)))
            ->orderBy('sc.name', 'ASC');
    }

    /**
     * @param AclHelper $aclHelper
     * @return SalesChannel[]
     */
    public function getActiveChannels(AclHelper $aclHelper)
    {
        $qb = $this->getActiveChannelsQuery();

        return $aclHelper->apply($qb)->getResult();
    }

    /**
     * @param AclHelper $aclHelper
     * @return SalesChannel[]
     */
    public function getDefaultActiveChannels(AclHelper $aclHelper)
    {
        $qb = $this->getActiveChannelsQuery();
        $qb->andWhere($qb->expr()->eq('sc.default', $qb->expr()->literal(true)));

        return $aclHelper->apply($qb)->getResult();
    }

    /**
     * @param Product $product
     * @return SalesChannel[]
     */
    public function findByProduct(Product $product)
    {
        $qb = $this->createQueryBuilder('sc')
            ->where(':product MEMBER OF sc.products')
            ->setParameters(['product' => $product]);

        return $qb->getQuery()->getResult();
    }
}
