<?php

namespace Marello\Bundle\SalesBundle\EventListener\Doctrine;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Oro\Bundle\DistributionBundle\Handler\ApplicationState;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class SalesChannelListener
{
    public function __construct(
        protected ApplicationState $applicationState,
        protected AclHelper $aclHelper
    ) {
    }

    public function prePersist(SalesChannel $salesChannel, LifecycleEventArgs $args)
    {
        if ($this->applicationState->isInstalled() && !$salesChannel->getGroup()) {
            $systemGroup = $args
                ->getObjectManager()
                ->getRepository(SalesChannelGroup::class)
                ->findSystemChannelGroup($this->aclHelper);

            if ($systemGroup) {
                $salesChannel->setGroup($systemGroup);
            }
        }
    }
}
