<?php

namespace Marello\Bundle\InventoryBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;

class ProductInventoryLogListener
{
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof InventoryItem) {
            return;
        }

        if ($args->hasChangedField('product')) {
            // TODO: If this works .. it's great!
        }
    }
}
