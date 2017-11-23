<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextFactory;
use Marello\Bundle\InventoryBundle\Form\EventListener\InventoryLevelSubscriber as BaseInventoryLevelSubscriber;

class InventoryLevelSubscriber extends BaseInventoryLevelSubscriber
{
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

        $context = InventoryUpdateContextFactory::createInventoryLevelUpdateContext(
            $inventoryLevel,
            $inventoryLevel->getInventoryItem(),
            $adjustment,
            0,
            'manual'
        );

        if (!$inventoryLevel->getId()) {
            $warehouse = $this->getWarehouse($form);
            $inventoryLevel->setWarehouse($warehouse);
            $context->setValue('warehouse', $warehouse);
        }

        $this->eventDispatcher->dispatch(
            InventoryUpdateEvent::NAME,
            new InventoryUpdateEvent($context)
        );

        $event->setData($inventoryLevel);
    }

    /**
     * @param FormInterface $form
     *
     * @return Warehouse
     */
    protected function getWarehouse(FormInterface $form)
    {
        if (!$form->has('warehouse')) {
            throw new \InvalidArgumentException(sprintf('%s form child is missing'));
        }

        return $form->get('warehouse')->getData();
    }
}
