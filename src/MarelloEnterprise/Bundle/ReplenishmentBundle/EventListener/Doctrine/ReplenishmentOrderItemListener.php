<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
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
        $em = $args->getEntityManager();
        $changes = $em->getUnitOfWork()->getEntityChangeSet($item);
        if (isset($changes['inventoryQty']) &&
            $changes['inventoryQty'][0] !== null &&
            $changes['inventoryQty'][0] !== $changes['inventoryQty'][1]) {
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
                InventoryUpdateEvent::NAME,
                new InventoryUpdateEvent($context)
            );
        }
    }
}
