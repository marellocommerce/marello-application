<?php

namespace Marello\Bundle\InventoryBundle\Form\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class InventoryItemSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ManagerRegistry $doctrine,
        private AclHelper $aclHelper
    ) {
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
            ->findExternalLevelsForInventoryItem($inventoryItem, $this->aclHelper);
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
