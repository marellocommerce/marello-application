<?php

namespace Marello\Bundle\PackingBundle\EventListener\Doctrine;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowData;
use Oro\Component\Action\Event\ExtendableActionEvent;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\PackingBundle\Entity\PackingSlip;
use Marello\Bundle\PackingBundle\Entity\PackingSlipItem;
use Marello\Bundle\OrderBundle\Model\OrderItemStatusesInterface;
use Marello\Bundle\OrderBundle\Migrations\Data\ORM\LoadOrderItemStatusData;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;

class PackingSlipItemStatusListener
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
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof PackingSlipItem) {
            $warehouse = $entity->getPackingSlip()->getWarehouse();
            $warehouseType = $warehouse->getWarehouseType()->getName();
            $inventoryItem = $entity->getProduct()->getInventoryItem();
            if ($inventoryItem) {
                $inventoryLevel = $inventoryItem->getInventoryLevel($warehouse);
                if ($inventoryLevel) {
                    if ($warehouseType === WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL) {
                        if ($inventoryLevel->getVirtualInventoryQty() >= $entity->getQuantity() ||
                            $inventoryLevel->isManagedInventory() === false) {
                            $entity->setStatus($this->findStatus(LoadOrderItemStatusData::DROPSHIPPING));
                        } else {
                            $entity->setStatus($this->findStatus(LoadOrderItemStatusData::COULD_NOT_ALLOCATE));
                        }
                    } else {
                        if ($inventoryLevel->getVirtualInventoryQty() >= $entity->getQuantity()) {
                            $entity->setStatus($this->findStatus(OrderItemStatusesInterface::OIS_COMPLETE));
                        }
                    }
                }
            }
            // tmp status update for packingslip item
            $entity->setStatus($this->findStatus(OrderItemStatusesInterface::OIS_COMPLETE));
        }
    }

    /**
     * @param ExtendableActionEvent $event
     */
    public function onOrderShipped(ExtendableActionEvent $event)
    {
        if (!$this->isCorrectOrderContext($event->getContext())) {
            return;
        }
        /** @var Order $entity */
        $order = $event->getContext()->getData()->get('order');
        $packingSlips = $this->doctrineHelper
            ->getEntityManagerForClass(PackingSlip::class)
            ->getRepository(PackingSlip::class)
            ->findBy(['order' => $order]);
        $entityManager = $this->doctrineHelper->getEntityManagerForClass(PackingSlipItem::class);
        foreach ($packingSlips as $packingSlip) {
            foreach ($packingSlip->getItems() as $item) {
                $item->setStatus($this->findStatus(OrderItemStatusesInterface::OIS_COMPLETE));
                $entityManager->persist($item);
            }
        }
        $entityManager->flush();
    }

    /**
     * @param mixed $context
     * @return bool
     */
    protected function isCorrectOrderContext($context)
    {
        return ($context instanceof WorkflowItem
            && $context->getData() instanceof WorkflowData
            && $context->getData()->has('order')
            && $context->getData()->get('order') instanceof Order
        );
    }

    /**
     * @param string $status
     * @return null|object
     */
    private function findStatus($status)
    {
        $returnReasonClass = ExtendHelper::buildEnumValueClassName(LoadOrderItemStatusData::ITEM_STATUS_ENUM_CLASS);
        $status = $this->doctrineHelper
            ->getEntityManagerForClass($returnReasonClass)
            ->getRepository($returnReasonClass)
            ->find($status);

        if ($status) {
            return $status;
        }

        return null;
    }
}
