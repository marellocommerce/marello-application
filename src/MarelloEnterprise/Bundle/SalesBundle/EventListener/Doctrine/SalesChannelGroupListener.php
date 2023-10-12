<?php

namespace MarelloEnterprise\Bundle\SalesBundle\EventListener\Doctrine;

use Doctrine\Persistence\Event\LifecycleEventArgs;

use Symfony\Component\HttpFoundation\Session\Session;

use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;

class SalesChannelGroupListener
{
    public function __construct(
        protected Session $session
    ) {
    }
    
    /**
     * @param LifecycleEventArgs $args
     * @throws \Exception
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if (!$entity instanceof SalesChannelGroup) {
            return;
        }
        $linkOwner = $args
            ->getObjectManager()
            ->getRepository(WarehouseChannelGroupLink::class)
            ->findLinkBySalesChannelGroup($entity);
        if ($linkOwner && !$linkOwner->isSystem()) {
            $this->session
                ->getFlashBag()
                ->add(
                    'error',
                    'It is forbidden to delete a Sales Channel(Group) linked to a WarehouseGroup, unlink it first'
                );
            throw new \Exception();
        }
    }
}
