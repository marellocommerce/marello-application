<?php

namespace Marello\Bundle\InventoryBundle\Form\EventListener;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryLevelCalculator;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextFactory;

class InventoryLevelSubscriber implements EventSubscriberInterface
{
    /**
     * @var InventoryLevelCalculator
     */
    protected $levelCalculator;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * InventoryLevelSubscriber constructor.
     * @param InventoryLevelCalculator $levelCalculator
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        InventoryLevelCalculator $levelCalculator,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->levelCalculator = $levelCalculator;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Get subscribed events
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::POST_SUBMIT     => 'handleUnMappedFields'
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function handleUnMappedFields(FormEvent $event)
    {
        /** @var InventoryLevel $inventoryLevel */
        $inventoryLevel = $event->getData();
        if (!$this->isApplicable($inventoryLevel)) {
            return;
        }

        $form = $event->getForm();
        if (!$form->has('adjustmentOperator') || !$form->has('quantity')) {
            return;
        }

        $operator = $this->getAdjustmentOperator($form);
        $quantity = $this->getAdjustmentQuantity($form);
        $adjustment = $this->levelCalculator->calculateAdjustment($operator, $quantity);
        if ($adjustment === 0) {
            return;
        }
        if (!$inventoryLevel->getInventoryItem()) {
            $inventoryItem = $this->getInventoryItem($event);
            if ($inventoryItem) {
                $inventoryLevel->setInventoryItem($inventoryItem);
            }
        }
        $context = InventoryUpdateContextFactory::createInventoryLevelUpdateContext(
            $inventoryLevel,
            $inventoryLevel->getInventoryItem(),
            [],
            $adjustment,
            0,
            'manual'
        );

        $this->eventDispatcher->dispatch(
            new InventoryUpdateEvent($context),
            InventoryUpdateEvent::NAME
        );
    }

    /**
     * @param FormInterface $form
     *
     * @return bool
     */
    protected function getAdjustmentOperator(FormInterface $form)
    {
        if (!$form->has('adjustmentOperator')) {
            throw new \InvalidArgumentException(sprintf('%s form child is missing', 'adjustmentOperator'));
        }

        return $form->get('adjustmentOperator')->getData();
    }

    /**
     * @param FormInterface $form
     *
     * @return bool
     */
    protected function getAdjustmentQuantity(FormInterface $form)
    {
        if (!$form->has('quantity')) {
            throw new \InvalidArgumentException(sprintf('%s form child is missing', 'quantity'));
        }

        return $form->get('quantity')->getData();
    }

    /**
     * @param InventoryLevel $inventoryLevel
     *
     * @return bool
     */
    protected function isApplicable(InventoryLevel $inventoryLevel = null)
    {
        if (!$inventoryLevel) {
            return false;
        }

        return true;
    }

    /**
     * @param FormEvent $event
     * @return InventoryItem
     */
    protected function getInventoryItem(FormEvent $event)
    {
        $form = $event->getForm();
        $inventoryLevel = $event->getData();
        if ($inventoryItem = $inventoryLevel->getInventoryItem()) {
            return $inventoryItem;
        }
        $collectionForm = $form->getParent();
        if ($collectionForm) {
            $inventoryItemForm = $collectionForm->getParent();
            if ($inventoryItemForm) {
                $inventoryItem = $inventoryItemForm->getData();
                if ($inventoryItem instanceof InventoryItem) {
                    return $inventoryItem;
                }
            }
        }

        return null;
    }

    /**
     * Determine whether whe should disable the InventoryLevel's ability to be updated
     * If the inventory is managed, we should not disable the fields related to updating the InventoryLevel
     * @param InventoryLevel $inventoryLevel
     * @return bool
     */
    protected function isInventoryLevelDisabled(InventoryLevel $inventoryLevel)
    {
        if ($inventoryLevel->isManagedInventory()) {
            return false;
        }

        return true;
    }
}
