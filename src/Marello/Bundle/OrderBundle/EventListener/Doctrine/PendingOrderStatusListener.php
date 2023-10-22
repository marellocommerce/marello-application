<?php

namespace Marello\Bundle\OrderBundle\EventListener\Doctrine;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Model\OrderStatusesInterface;

class PendingOrderStatusListener
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
        $entity = $args->getObject();
        if ($entity instanceof Order && $entity->getOrderStatus() === null) {
            $className = ExtendHelper::buildEnumValueClassName(OrderStatusesInterface::ORDER_STATUS_ENUM_CLASS);
            /** @var EnumValueRepository $enumRepo */
            $enumRepo = $this->doctrineHelper
                ->getEntityManagerForClass($className)
                ->getRepository($className);

            $orderStatus = $enumRepo->findOneBy(['id' => 'pending']);
            $entity->setOrderStatus($orderStatus);
        }
    }
}
