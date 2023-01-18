<?php

namespace MarelloEnterprise\Bundle\SalesBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Oro\Bundle\DistributionBundle\Handler\ApplicationState;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

class OrganizationCreateListener
{
    public function __construct(
        protected ApplicationState $applicationState
    ) {
    }
    
    /**
     * @param Organization $organization
     * @param LifecycleEventArgs $args
     */
    public function postPersist(Organization $organization, LifecycleEventArgs $args)
    {
        if ($this->applicationState->isInstalled()) {
            $systemChannelGroup = new SalesChannelGroup();
            $systemChannelGroup
                ->setName(sprintf('%s System Group', $organization->getName()))
                ->setDescription(sprintf('System Sales Channel Group for %s organization', $organization->getName()))
                ->setSystem(true)
                ->setOrganization($organization);

            $em = $args->getEntityManager();
            $em->persist($systemChannelGroup);
            $em->flush();
        }
    }
}
