<?php

namespace Marello\Bundle\Magento2Bundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\Magento2Bundle\DTO\OrderIdentifierDTO;
use Marello\Bundle\Magento2Bundle\Entity\Hydrator\OrderIdentifierDTOHydrator;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

class OrderRepository extends EntityRepository
{
    /**
     * @param Channel $channel
     * @param array $marelloOrderIds
     * @return OrderIdentifierDTO[]
     */
    public function getOrdersIdentifierDTOsByChannelAndOrderIds(Channel $channel, array $marelloOrderIds): array
    {
        $qb = $this->createQueryBuilder('m2o');
        $qb
            ->select('m2o.id as magentoOrderId', 'IDENTITY(m2o.innerOrder) as marelloOrderId')
            ->where($qb->expr()->eq('m2o.channel', ':channel'))
            ->andWhere($qb->expr()->in('m2o.innerOrder', ':marelloOrderIds'))
            ->setParameter('channel', $channel)
            ->setParameter('marelloOrderIds', $marelloOrderIds);

        $this->_em
            ->getConfiguration()
            ->addCustomHydrationMode(
                'OrderIdentifierDTOHydrator',
                OrderIdentifierDTOHydrator::class
            );

        return $qb->getQuery()->getResult('OrderIdentifierDTOHydrator');
    }
}
