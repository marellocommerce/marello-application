<?php

namespace MarelloEnterprise\Bundle\SalesBundle\EventListener\Doctrine;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Oro\Bundle\DistributionBundle\Handler\ApplicationState;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

class OrganizationCreateListener
{
    /**
     * @param Organization $organization
     * @param LifecycleEventArgs $args
     */
    public function postPersist(Organization $organization, LifecycleEventArgs $args)
    {
        $em = $args->getObjectManager();
        $existingGroup = $em
            ->getRepository(SalesChannelGroup::class)
            ->findOneBy([
                'system' => true,
                'organization'=> $organization
            ]);
        $systemChannelGroup = ($existingGroup) ?: new SalesChannelGroup();
        $systemChannelGroup
            ->setName(sprintf('%s System Group', $organization->getName()))
            ->setDescription(sprintf('System Sales Channel Group for %s organization', $organization->getName()))
            ->setSystem(true)
            ->setOrganization($organization);

        $em->persist($systemChannelGroup);
        $em->flush();
    }
}
