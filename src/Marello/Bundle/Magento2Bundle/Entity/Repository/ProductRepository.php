<?php

namespace Marello\Bundle\Magento2Bundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Marello\Bundle\Magento2Bundle\DTO\ProductIdentifierDTO;
use Marello\Bundle\Magento2Bundle\Entity\Hydrator\ProductIdentifierDTOHydrator;
use Marello\Bundle\Magento2Bundle\Entity\Product;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

class ProductRepository extends EntityRepository
{
    /**
     * @param Channel $channel
     * @param array $marelloProductIds
     * @return ProductIdentifierDTO[]
     */
    public function getProductIdentifierDTOsByChannelAndProductIds(Channel $channel, array $marelloProductIds): array
    {
        $qb = $this->createQueryBuilder('m2p');
        $qb
            ->select('m2p.id as magentoProductId', 'IDENTITY(m2p.product) as marelloProductId')
            ->where($qb->expr()->eq('m2p.channel', ':channel'))
            ->andWhere($qb->expr()->in('m2p.product', ':productIds'))
            ->setParameter('channel', $channel)
            ->setParameter('productIds', $marelloProductIds);

        $this->_em
            ->getConfiguration()
            ->addCustomHydrationMode(
                'ProductIdentifierDTOHydrator',
                ProductIdentifierDTOHydrator::class
            );

        return $qb->getQuery()->getResult('ProductIdentifierDTOHydrator');
    }

    /**
     * @param int $channelId
     * @param int $marelloProductId
     * @return Product|null
     * @throws NonUniqueResultException
     */
    public function getMagentoProductByChannelIdAndProductId(int $channelId, int $marelloProductId): ?Product
    {
        $qb = $this->createQueryBuilder('m2p');
        $qb
            ->select('m2p')
            ->where($qb->expr()->eq('m2p.channel', ':channel'))
            ->andWhere($qb->expr()->eq('m2p.product', ':product'))
            ->setParameter('channel', $channelId)
            ->setParameter('product', $marelloProductId);

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
     *     int <marello_product_id> => string <product_sku>,
     *     ...
     * ]
     */
    public function getMarelloProductIdsWithSKUsByIntegration(Channel $channel): array
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
