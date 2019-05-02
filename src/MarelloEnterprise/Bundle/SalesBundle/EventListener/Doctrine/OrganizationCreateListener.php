<?php

namespace MarelloEnterprise\Bundle\SalesBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

class OrganizationCreateListener
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
     * @param Organization $organization
     * @param LifecycleEventArgs $args
     */
    public function postPersist(Organization $organization, LifecycleEventArgs $args)
    {
        if ($this->installed) {
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
