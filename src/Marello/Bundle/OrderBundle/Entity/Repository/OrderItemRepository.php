<?php

namespace Marello\Bundle\OrderBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\OrderBundle\Entity\OrderItem;

class OrderItemRepository extends EntityRepository
{
    public function getOrderItemsQueryBuilder($orderId)
    {
        return $this->createQueryBuilder('oi')
            ->andWhere('IDENTITY(oi.order) = :orderId')
            ->setParameter('orderId', $orderId);
    }

    /**
     * @param $orderId
     * @param $itemId
     *
     * @return null|OrderItem
     */
    public function findByOrderAndId($orderId, $itemId)
    {
        return $this->findOneBy([
            'order' => $orderId,
            'id'    => $itemId,
        ]);
    }
}
