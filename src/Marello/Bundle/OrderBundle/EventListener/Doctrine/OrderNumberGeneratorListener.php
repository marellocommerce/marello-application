<?php

namespace Marello\Bundle\OrderBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Marello\Bundle\NotificationBundle\Email\SendProcessor;
use Marello\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink;

class OrderNumberGeneratorListener
{
    /** @var Order[] */
    protected $orders = [];

    /** @var ServiceLink */
    protected $emailSendProcessorLink;

    /**
     * OrderNumberGeneratorListener constructor.
     *
     * @param ServiceLink $emailSendProcessorLink
     */
    public function __construct(ServiceLink $emailSendProcessorLink)
    {
        $this->emailSendProcessorLink = $emailSendProcessorLink;
    }

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
        if (empty($this->orders)) {
            return;
        }

        $em            = $args->getEntityManager();
        $changedOrders = $this->updateOrderNumbers($this->orders);

        /*
         * Empty orders array to indicate that all orders have been process and to prevent loop on flushing.
         */
        $createdOrders = $this->orders;
        $this->orders = [];

        foreach ($changedOrders as $order) {
            $em->persist($order);
        }

        /** @var SendProcessor $emailSendProcessor */
        $emailSendProcessor = $this->emailSendProcessorLink->getService();

        foreach ($createdOrders as $order) {
            $emailSendProcessor->sendNotification(
                'marello_order_accepted_confirmation',
                [$order->getBillingAddress()->getEmail()],
                $order
            );
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
