<?php

namespace Marello\Bundle\SalesBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;

class SalesChannelSystemGroupListener
{
    /**
     * @param SalesChannel $salesChannel
     * @param LifecycleEventArgs $args
     */
    public function prePersist(SalesChannel $salesChannel, LifecycleEventArgs $args)
    {
        $systemGroup = $args
            ->getEntityManager()
            ->getRepository(SalesChannelGroup::class)
            ->findOneBy(['system' => true]);
        
        if ($systemGroup) {
            $salesChannel->setGroup($systemGroup);
        }
    }
}
