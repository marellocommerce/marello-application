<?php

namespace Marello\Bundle\SalesBundle\EventListener\Doctrine;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Oro\Bundle\DistributionBundle\Handler\ApplicationState;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Symfony\Component\HttpFoundation\Session\Session;
use Oro\Bundle\EntityBundle\ORM\OroEntityManager;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SalesChannelGroupListener
{
    public function __construct(
        protected ApplicationState $applicationState,
        protected Session $session,
        protected AclHelper $aclHelper
    ) {
    }
    
    /**
     * @var WarehouseChannelGroupLink
     */
    private $systemWarehouseChannelGroupLink;

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if (!$entity instanceof SalesChannelGroup) {
            return;
        }
        if ($entity->isSystem()) {
            $message = 'It is forbidden to delete system Sales Channel Group';
            $this->session
                ->getFlashBag()
                ->add('error', $message);
            throw new AccessDeniedException($message);
        }
        $em = $args->getObjectManager();
        $systemGroup = $em
            ->getRepository(SalesChannelGroup::class)
            ->findSystemChannelGroup($this->aclHelper);
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
        if ($this->applicationState->isInstalled()) {
            $em = $args->getObjectManager();
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
