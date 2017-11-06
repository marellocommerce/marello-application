<?php

namespace Marello\Bundle\SalesBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;

class SalesChannelListener
{
    /**
     * Installed flag
     *
     * @var bool
     */
    protected $installed;

    /**
     * @param bool $installed
     */
    public function __construct($installed)
    {
        $this->installed = $installed;
    }

    /**
     * @param SalesChannel $salesChannel
     * @param LifecycleEventArgs $args
     */
    public function prePersist(SalesChannel $salesChannel, LifecycleEventArgs $args)
    {
        if ($this->installed && !$salesChannel->getGroup()) {
            $systemGroup = $args
                ->getEntityManager()
                ->getRepository(SalesChannelGroup::class)
                ->findSystemChannelGroup();

            if ($systemGroup) {
                $salesChannel->setGroup($systemGroup);
            }
        }
    }
}
