<?php

namespace Marello\Bundle\InventoryBundle\Form\EventListener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class InventoryItemSubscriber implements EventSubscriberInterface
{
    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * Get subscribed events
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::SUBMIT => 'submit'
        ];
    }
    
    /**
     * @param FormEvent $event
     */
    public function submit(FormEvent $event)
    {
        /** @var InventoryItem $inventoryItem */
        $inventoryItem = $event->getData();
        $externalInventroyLevels = $this->doctrine
            ->getManagerForClass(InventoryLevel::class)
            ->getRepository(InventoryLevel::class)
            ->findExternalLevelsForInventoryItem($inventoryItem);
        if (!empty($externalInventroyLevels)) {
            foreach ($externalInventroyLevels as $inventoryLevel) {
                if (!$inventoryItem->getInventoryLevel($inventoryLevel->getWarehouse())) {
                    $inventoryItem->addInventoryLevel($inventoryLevel);
                }
            }
        }
        $event->setData($inventoryItem);
    }
}
