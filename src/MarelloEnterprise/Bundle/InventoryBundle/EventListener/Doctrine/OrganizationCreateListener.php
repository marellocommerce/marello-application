<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

class OrganizationCreateListener
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

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
            $this->entityManager = $args->getEntityManager();
            $this->createSystemWarehouseGroupForOrganization($organization);
            $this->createSystemWarehouseChannelGroupLinkForOrganization($organization);
        }
    }

    /**
     * @param Organization $organization
     */
    private function createSystemWarehouseGroupForOrganization(Organization $organization)
    {
        $systemWhGroup = new WarehouseGroup();
        $systemWhGroup
            ->setName(sprintf('%s System Group', $organization->getName()))
            ->setDescription(sprintf('System Warehouse Group for %s organization', $organization->getName()))
            ->setSystem(true)
            ->setOrganization($organization);

        $this->entityManager->persist($systemWhGroup);
        $this->entityManager->flush();
    }

    /**
     * @param Organization $organization
     */
    private function createSystemWarehouseChannelGroupLinkForOrganization(Organization $organization)
    {
        $systemWarehouseGroup = $this->entityManager
            ->getRepository(WarehouseGroup::class)
            ->findOneBy([
                'name' => sprintf('%s_system_group', str_replace(' ', '_', strtolower($organization->getName())))
            ]);
        $systemChannelGroup = $this->entityManager
            ->getRepository(SalesChannelGroup::class)
            ->findOneBy([
                'name' => sprintf('%s_system_group', str_replace(' ', '_', strtolower($organization->getName())))
            ]);

        if ($systemWarehouseGroup && $systemChannelGroup) {
            $systemLink = new WarehouseChannelGroupLink();
            $systemLink
                ->setWarehouseGroup($systemWarehouseGroup)
                ->addSalesChannelGroup($systemChannelGroup)
                ->setOrganization($organization)
                ->setSystem(true);

            $this->entityManager->persist($systemLink);
            $this->entityManager->flush();
        }
    }
}
