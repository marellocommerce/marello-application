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
    public function getMagentoProductByChannelAndProductIds(Channel $channel, array $productIds)
    {
        $qb = $this->createQueryBuilder('m2p');
        $qb
            ->select('m2p')
            ->where($qb->expr()->eq('m2p.channel', ':channel'))
            ->andWhere($qb->expr()->in('m2p.product', ':productIds'))
            ->setParameter('channel', $channel)
            ->setParameter('productIds', $productIds)
        ;

        return $qb->getQuery()->getResult();
    }
}
