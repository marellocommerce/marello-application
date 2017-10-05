<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;

class WarehouseChannelGroupLinkListener
{
    /**
     * @param WarehouseChannelGroupLink $warehouseChannelGroupLink
     * @param LifecycleEventArgs $args
     */
    public function prePersist(WarehouseChannelGroupLink $warehouseChannelGroupLink, LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        $systemLink = $em
            ->getRepository(WarehouseChannelGroupLink::class)
            ->findOneBy(['system' => true]);

        if ($systemLink) {
            foreach ($warehouseChannelGroupLink->getSalesChannelGroups() as $salesChannelGroup) {
                $systemLink->removeSalesChannelGroup($salesChannelGroup);
            }
            $em->persist($systemLink);
            $em->flush();
        }
    }

    /**
     * @param WarehouseChannelGroupLink $warehouseChannelGroupLink
     * @param LifecycleEventArgs $args
     */
    public function postRemove(WarehouseChannelGroupLink $warehouseChannelGroupLink, LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        $systemLink = $em
            ->getRepository(WarehouseChannelGroupLink::class)
            ->findOneBy(['system' => true]);

        if ($systemLink) {
            foreach ($warehouseChannelGroupLink->getSalesChannelGroups() as $salesChannelGroup) {
                $systemLink->addSalesChannelGroup($salesChannelGroup);
            }
            $em->persist($systemLink);
            $em->flush();
        }
    }
}
