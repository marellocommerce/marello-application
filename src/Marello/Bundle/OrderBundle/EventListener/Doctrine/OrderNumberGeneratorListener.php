<?php

namespace Marello\Bundle\OrderBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Marello\Bundle\OrderBundle\Entity\Order;

class OrderNumberGeneratorListener
{
    /** @var Order[] */
    protected $orders = [];

    /**
     * Collects all orders scheduled for insertion.
     * Only insertion is used because we don't need to generate order numbers in case it is changed later.
     *
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $uow        = $args->getEntityManager()->getUnitOfWork();
        $insertions = $uow->getScheduledEntityInsertions();

        foreach ($insertions as $entity) {
            if ($entity instanceof Order) {
                $this->orders[] = $entity;
            }
        }
    }

    /**
     * Updates all orders which have been scheduled for insertion.
     *
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $em            = $args->getEntityManager();
        $changedOrders = $this->updateOrderNumbers($this->orders);

        /*
         * Empty orders array to indicate that all orders have been process and to prevent loop on flushing.
         */
        $this->orders = [];

        foreach ($changedOrders as $order) {
            $em->persist($order);
        }

        if (!empty($changedOrders)) {
            $em->flush($changedOrders);
        }
    }

    /**
     * Update order numbers for all orders which still don' have it.
     * Order number is generated using ID.
     *
     * @param Order[] $orders
     *
     * @return Order[] Array of changed orders.
     */
    protected function updateOrderNumbers(array $orders)
    {
        $changedOrders = [];
        foreach ($orders as $order) {
            if (!$order->getOrderNumber()) {
                $changedOrders[] = $order->setOrderNumber(sprintf('%09d', $order->getId()));
            }
        }

        return $changedOrders;
    }
}
