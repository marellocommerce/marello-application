<?php

namespace Marello\Bundle\InventoryBundle\Form\EventListener;

use Marello\Bundle\InventoryBundle\Entity\InventoryBatch;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryLevelCalculator;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextFactory;

class InventoryBatchSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected InventoryLevelCalculator $levelCalculator,
        protected EventDispatcherInterface $eventDispatcher,
        protected array $previousSellByDate = []
    ) {}

    /**
     * Get subscribed events
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::POST_SET_DATA => 'storeOldData',
            FormEvents::POST_SUBMIT => 'handleUnMappedFields'
        ];
    }

    public function storeOldData(FormEvent $event)
    {
        $data = $event->getData();
        if (!$data) {
            return;
        }

        $this->previousSellByDate[spl_object_id($event->getForm())] = $data->getSellByDate();
    }

    /**
     * @param FormEvent $event
     */
    public function handleUnMappedFields(FormEvent $event)
    {
        /** @var InventoryBatch $inventoryBatch */
        $inventoryBatch = $event->getData();
        $form = $event->getForm();
        /** @var InventoryLevel $inventoryLevel */
        $inventoryLevel = $inventoryBatch->getInventoryLevel();
        if (!$inventoryLevel) {
            $parentData = $form->getParent()->getParent()->getData();
            if ($parentData instanceof InventoryLevel) {
                $inventoryLevel = $parentData;
                $inventoryBatch->setInventoryLevel($inventoryLevel);
            } else {
                return;
            }
        }

        $form = $event->getForm();
        if (!$form->has('adjustmentOperator') || !$form->has('adjustmentQuantity')) {
            return;
        }

        $operator = $this->getAdjustmentOperator($form);
        $quantity = $this->getAdjustmentQuantity($form);
        $adjustment = $this->levelCalculator->calculateAdjustment($operator, $quantity);
        $isSellByDateChanged = array_key_exists(spl_object_id($event->getForm()), $this->previousSellByDate)
            && $this->previousSellByDate[spl_object_id($event->getForm())] != $event->getData()->getSellByDate();
        if ($adjustment === 0 && !$isSellByDateChanged) {
            return;
        }
        $batches = [
            [
                'batch' => $inventoryBatch,
                'qty' => $adjustment
            ]
        ];
        $context = InventoryUpdateContextFactory::createInventoryLevelUpdateContext(
            $inventoryLevel,
            $inventoryLevel->getInventoryItem(),
            $batches,
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
        if (!$form->has('adjustmentQuantity')) {
            throw new \InvalidArgumentException(sprintf('%s form child is missing', 'adjustmentQuantity'));
        }

        return $form->get('adjustmentQuantity')->getData();
    }
}
