<?php

namespace Marello\Bundle\Magento2Bundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\Magento2Bundle\Entity\Product;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

class ProductRepository extends EntityRepository
{
    /**
     * @param Channel $channel
     * @param array $productIds
     * @return Product[]
     */
    public function getMagentoProductByChannelAndProductIds(Channel $channel, array $productIds): array
    {
        $qb = $this->createQueryBuilder('m2p');
        $qb
            ->select('m2p')
            ->where($qb->expr()->eq('m2p.channel', ':channel'))
            ->andWhere($qb->expr()->in('m2p.product', ':productIds'))
            ->setParameter('channel', $channel)
            ->setParameter('productIds', $productIds);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param int $channelId
     * @param int $productId
     * @return Product|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getMagentoProductByChannelIdAndProductId(int $channelId, int $productId): ?Product
    {
        $qb = $this->createQueryBuilder('m2p');
        $qb
            ->select('m2p')
            ->where($qb->expr()->eq('m2p.channel', ':channel'))
            ->andWhere($qb->expr()->eq('m2p.product', ':product'))
            ->setParameter('channel', $channelId)
            ->setParameter('product', $productId);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param int $channelId
     */
    public function deleteByIntegrationId(int $channelId): void
    {
        $qb = $this->createQueryBuilder('m2p');
        $qb->delete()
            ->where(
                $qb->expr()->eq('m2p.channel', ':channel')
            )
            ->setParameter('channel', $channelId);

        $qb->getQuery()->execute();
    }

    /**
     * @param Channel $channel
     * @return array
     * [
     *     int <product_id> => string <product_sku>,
     *     ...
     * ]
     */
    public function getOriginalProductIdsWithSKUsByIntegration(Channel $channel): array
    {
        $qb = $this->createQueryBuilder('m2p');
        $qb
            ->select(['IDENTITY(m2p.product) as id', 'm2p.sku'])
            ->where($qb->expr()->eq('m2p.channel', ':channel'))
            ->setParameter('channel', $channel);

        $result = $qb->getQuery()->getArrayResult();

        return \array_combine(
            \array_column($result, 'id'),
            \array_column($result,'sku')
        );
    }
}
