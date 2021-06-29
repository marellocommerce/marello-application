<?php

namespace Marello\Bundle\SalesBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;

use Symfony\Component\HttpFoundation\Session\Session;

use Oro\Bundle\EntityBundle\ORM\OroEntityManager;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;

use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;

class SalesChannelGroupListener
{
    /**
     * Installed flag
     *
     * @var bool
     */
    protected $installed;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @param bool $installed
     * @param Session $session
     */
    public function __construct($installed, Session $session)
    {
        $this->installed = $installed;
        $this->session = $session;
    }
    
    /**
     * @var WarehouseChannelGroupLink
     */
    private $systemWarehouseChannelGroupLink;

    /**
     * @param LifecycleEventArgs $args
     * @throws ForbiddenException
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof SalesChannelGroup) {
            return;
        }
        if ($entity->isSystem()) {
            $message = 'It is forbidden to delete system Sales Channel Group';
            $this->session
                ->getFlashBag()
                ->add('error', $message);
            throw new ForbiddenException($message);
        }
        $em = $args->getEntityManager();
        $systemGroup = $em
            ->getRepository(SalesChannelGroup::class)
            ->findSystemChannelGroup();
        $systemWarehouseChannelGroupLink = $this->getSystemWarehouseChannelGroupLink($em);

        if (!$systemGroup && !$systemWarehouseChannelGroupLink) {
            return;
        }
        if ($systemGroup) {
            $salesChannels = $entity->getSalesChannels();
            foreach ($salesChannels as $salesChannel) {
                $salesChannel->setGroup($systemGroup);
                $em->persist($salesChannel);
            }
        }
        if ($systemWarehouseChannelGroupLink) {
            $systemWarehouseChannelGroupLink->removeSalesChannelGroup($entity);
        }
        
        $em->flush();
    }

    /**
     * @param SalesChannelGroup $salesChannelGroup
     * @param LifecycleEventArgs $args
     */
    public function postPersist(SalesChannelGroup $salesChannelGroup, LifecycleEventArgs $args)
    {
        if ($this->installed) {
            $em = $args->getEntityManager();
            $systemWarehouseChannelGroupLink = $this->getSystemWarehouseChannelGroupLink($em);

            if ($systemWarehouseChannelGroupLink) {
                $systemWarehouseChannelGroupLink->addSalesChannelGroup($salesChannelGroup);

                $em->persist($systemWarehouseChannelGroupLink);
                $em->flush();
            }
        }
    }

    /**
     * @param OroEntityManager $entityManager
     * @return WarehouseChannelGroupLink|null
     */
    private function getSystemWarehouseChannelGroupLink(OroEntityManager $entityManager)
    {
        if ($this->systemWarehouseChannelGroupLink === null) {
            $this->systemWarehouseChannelGroupLink = $entityManager
                ->getRepository(WarehouseChannelGroupLink::class)
                ->findSystemLink();
        }

        return $this->systemWarehouseChannelGroupLink;
    }
}
