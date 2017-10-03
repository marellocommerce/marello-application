<?php

namespace Marello\Bundle\SalesBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;

class SalesChannelGroupRemoveListener
{
    /**
     * @param SalesChannelGroup $salesChannelGroup
     * @param LifecycleEventArgs $args
     */
    public function preRemove(SalesChannelGroup $salesChannelGroup, LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        $systemGroup = $em
            ->getRepository(SalesChannelGroup::class)
            ->findOneBy(['system' => true]);
        
        if ($systemGroup) {
            $salesChannels = $salesChannelGroup->getSalesChannels();
            foreach ($salesChannels as $salesChannel) {
                $salesChannel->setGroup($systemGroup);
                $em->persist($salesChannel);
            }
            $em->flush();
        }
    }
}
