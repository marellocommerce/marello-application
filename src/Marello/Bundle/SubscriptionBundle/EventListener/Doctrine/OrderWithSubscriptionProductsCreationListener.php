<?php

namespace Marello\Bundle\SubscriptionBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\SubscriptionBundle\Mapper\OrderToSubscriptionsMapper;

class OrderWithSubscriptionProductsCreationListener
{
    /**
     * @var OrderToSubscriptionsMapper
     */
    private $orderToSubscriptionsMapper;

    /**
     * @param OrderToSubscriptionsMapper $orderToSubscriptionsMapper
     */
    public function __construct(OrderToSubscriptionsMapper $orderToSubscriptionsMapper)
    {
        $this->orderToSubscriptionsMapper = $orderToSubscriptionsMapper;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Order && $this->hasSubscriptions($entity)) {
            $em = $args->getEntityManager();
            $uow = $em->getUnitOfWork();
            $subscriptions = $this->orderToSubscriptionsMapper->map($entity);
            foreach ($subscriptions as $subscription) {
                $uow->scheduleForInsert($subscription);
            }
        }
    }

    /**
     * @param Order $order
     * @return boolean
     */
    private function hasSubscriptions(Order $order)
    {
        $hasSubscriptions = false;
        foreach ($order->getItems() as $orderItem) {
            if ($orderItem->getProduct()->getType() === 'subscription') {
                return true;
            }
        }

        return $hasSubscriptions;
    }
}
