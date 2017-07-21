<?php

namespace Marello\Bundle\InventoryBundle\Form\EventListener;

use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Model\InventoryLevelCalculator;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextFactory;

class InventoryLevelSubscriber implements EventSubscriberInterface
{
    /** @var InventoryLevelCalculator $levelCalculator */
    protected $levelCalculator;

    /** @var EventDispatcherInterface $eventDispatcher */
    protected $eventDispatcher;

    /**
     * InventoryLevelSubscriber constructor.
     * @param InventoryLevelCalculator $levelCalculator
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        InventoryLevelCalculator $levelCalculator,
        EventDispatcherInterface $eventDispatcher
    ){
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
        $totalInventoryQuantity =
            $this->levelCalculator->calculateInventoryQuantity($inventoryLevel, $quantity, $operator);
        $inventoryLevel->setInventoryQty($totalInventoryQuantity);
        $event->setData($inventoryLevel);

        $context = InventoryUpdateContextFactory::createInventoryLevelUpdateContext(
            $inventoryLevel,
            $inventoryLevel->getInventoryItem(),
            $this->levelCalculator->calculateAdjustment($operator, $quantity),
            0,
            'manual',
            null
        );

        $this->eventDispatcher->dispatch(
            InventoryUpdateEvent::NAME,
            new InventoryUpdateEvent($context)
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
            throw new \InvalidArgumentException(sprintf('%s form child is missing'));
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
            throw new \InvalidArgumentException(sprintf('%s form child is missing'));
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
}
