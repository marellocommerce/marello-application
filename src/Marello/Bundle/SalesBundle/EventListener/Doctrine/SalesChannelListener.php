<?php

namespace Marello\Bundle\SalesBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class SalesChannelListener
{
    public function __construct(
        protected $installed,
        protected AclHelper $aclHelper
    ) {}

    public function prePersist(SalesChannel $salesChannel, LifecycleEventArgs $args)
    {
        if ($this->installed && !$salesChannel->getGroup()) {
            $systemGroup = $args
                ->getEntityManager()
                ->getRepository(SalesChannelGroup::class)
                ->findSystemChannelGroup($this->aclHelper);

            if ($systemGroup) {
                $salesChannel->setGroup($systemGroup);
            }
        }
    }
}
