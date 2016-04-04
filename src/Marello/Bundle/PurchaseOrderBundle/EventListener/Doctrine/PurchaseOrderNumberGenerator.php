<?php

namespace Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;

class PurchaseOrderNumberGenerator
{
    /** @var PurchaseOrder[] */
    protected $orders = [];

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $uow = $args->getEntityManager()->getUnitOfWork();

        $this->orders = array_filter(
            $uow->getScheduledEntityInsertions(),
            function ($entity) {
                return ($entity instanceof PurchaseOrder) && ($entity->getPurchaseOrderNumber() === null);
            }
        );
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if (empty($this->orders)) {
            return;
        }

        $this->generatePurchaseOrderNumbers();

        $toFlush = $this->orders;
        $this->orders = [];

        $args->getEntityManager()->flush($toFlush);
    }

    /**
     * Generate purchase order numbers for all currently stored POs.
     */
    protected function generatePurchaseOrderNumbers()
    {
        foreach ($this->orders as $order) {
            $order->setPurchaseOrderNumber(sprintf('%09d', $order->getId()));
        }
    }
}
