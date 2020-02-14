<?php

namespace MarelloEnterprise\Bundle\SalesBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Symfony\Component\HttpFoundation\Session\Session;

class SalesChannelGroupListener
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }
    
    /**
     * @param LifecycleEventArgs $args
     * @throws \Exception
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof SalesChannelGroup) {
            return;
        }
        $linkOwner = $args
            ->getEntityManager()
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
