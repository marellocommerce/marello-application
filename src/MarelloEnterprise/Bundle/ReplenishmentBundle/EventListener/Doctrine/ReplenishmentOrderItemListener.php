<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\EventListener\Doctrine;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Marello\Bundle\InventoryBundle\Entity\InventoryBatch;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextFactory;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderItem;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReplenishmentOrderItemListener
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param TranslatorInterface $translator
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        TranslatorInterface $translator
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->translator = $translator;
    }
    
    /**
     * @param ReplenishmentOrderItem $item
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(ReplenishmentOrderItem $item, LifecycleEventArgs $args)
    {
        $em = $args->getObjectManager();
        $changes = $em->getUnitOfWork()->getEntityChangeSet($item);
        if (!isset($changes['inventoryQty'])
            || $changes['inventoryQty'][0] === null
            || $changes['inventoryQty'][0] === $changes['inventoryQty'][1]
        ) {
            return;
        }

        $this->collectInventoryBatches($item, $changes);
        $diff = $changes['inventoryQty'][1] - $changes['inventoryQty'][0];
        $context = InventoryUpdateContextFactory::createInventoryUpdateContext(
            $item,
            null,
            null,
            $diff,
            $this->translator->trans(
                'marelloenterprise.replenishment.replenishmentorder.workflow.ready_for_shipping'
            )
        );

        $context->setValue('warehouse', $item->getOrder()->getOrigin());

        $this->eventDispatcher->dispatch(
            new InventoryUpdateEvent($context),
            InventoryUpdateEvent::NAME
        );
    }

    private function collectInventoryBatches(ReplenishmentOrderItem $item, array $changes): void
    {
        $product = $item->getProduct();
        if (!$product) {
            return;
        }

        $inventoryItem = $product->getInventoryItem();
        if (!$inventoryItem || !$inventoryItem->isEnableBatchInventory()) {
            return;
        }

        $origin = $item->getOrder()->getOrigin();
        $inventoryLevel = $inventoryItem->getInventoryLevel($origin);
        if (!$inventoryLevel) {
            return;
        }

        /** @var InventoryBatch[] $inventoryBatches */
        $inventoryBatches = $inventoryLevel->getInventoryBatches()->toArray();
        if (count($inventoryBatches) === 0) {
            return;
        }

        usort($inventoryBatches, function (InventoryBatch $a, InventoryBatch $b) {
            if ($a->getDeliveryDate() < $b->getDeliveryDate()) {
                return -1;
            } elseif ($a->getDeliveryDate() > $b->getDeliveryDate()) {
                return 1;
            } else {
                return 0;
            }
        });

        $data = [];
        $quantity = $changes['inventoryQty'][1];
        foreach ($inventoryBatches as $inventoryBatch) {
            if ($inventoryBatch->getQuantity() >= $quantity) {
                $data[$inventoryBatch->getBatchNumber()] = $quantity;
                break;
            } elseif ($batchQty = $inventoryBatch->getQuantity() > 0) {
                $data[$inventoryBatch->getBatchNumber()] = $batchQty;
                $quantity = $quantity - $batchQty;
            }
        }
        $item->setInventoryBatches($data);
    }
}
