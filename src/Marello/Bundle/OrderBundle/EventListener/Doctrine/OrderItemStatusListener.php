<?php

namespace Marello\Bundle\OrderBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\OrderBundle\Migrations\Data\ORM\LoadOrderItemStatusData;
use Marello\Bundle\PackingBundle\Entity\PackingSlipItem;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

class OrderItemStatusListener
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }


    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof OrderItem) {
            $entity->setStatus($this->findDefaultStatus());
        }
        if ($entity instanceof PackingSlipItem) {
            $orderItem = $entity->getOrderItem();
            $orderItem->setStatus($entity->getStatus());
        }
    }

    /**
     * @return null|object
     */
    private function findDefaultStatus()
    {
        $returnReasonClass = ExtendHelper::buildEnumValueClassName(LoadOrderItemStatusData::ITEM_STATUS_ENUM_CLASS);
        $status = $this->doctrineHelper
            ->getEntityManagerForClass($returnReasonClass)
            ->getRepository($returnReasonClass)
            ->findOneByDefault(true);

        if ($status) {
            return $status;
        }

        return null;
    }
}