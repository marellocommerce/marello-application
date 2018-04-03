<?php

namespace Marello\Bundle\InventoryBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesChannelGroupData;

class LoadWarehouseChannelLinkData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadSalesChannelGroupData::class
        ];
    }

    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $organization = $this->manager
            ->getRepository('OroOrganizationBundle:Organization')
            ->getFirst();

        $defaultWarehouseChannelGroupLink = $this->manager
            ->getRepository(WarehouseChannelGroupLink::class)
            ->findSystemLink();

        if ($defaultWarehouseChannelGroupLink) {
            return;
        }

        $systemWarehouseGroup = $this->manager
            ->getRepository(WarehouseGroup::class)
            ->findOneBy(['system' => true]);
        $systemSalesChannelGroup = $this->manager
            ->getRepository(SalesChannelGroup::class)
            ->findOneBy(['system' => true]);

        $defaultWarehouseChannelGroupLink = new WarehouseChannelGroupLink();
        $defaultWarehouseChannelGroupLink
            ->setSystem(true)
            ->setOrganization($organization)
            ->setWarehouseGroup($systemWarehouseGroup)
            ->addSalesChannelGroup($systemSalesChannelGroup);

        $this->manager->persist($defaultWarehouseChannelGroupLink);
        $this->manager->flush();
    }
}
