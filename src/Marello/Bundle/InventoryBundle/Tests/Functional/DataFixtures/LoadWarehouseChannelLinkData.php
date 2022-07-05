<?php

namespace Marello\Bundle\InventoryBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesChannelGroupData;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadWarehouseChannelLinkData extends AbstractFixture implements
    DependentFixtureInterface,
    ContainerAwareInterface
{
    use ContainerAwareTrait;

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

        /** @var AclHelper $aclHelper */
        $aclHelper = $this->container->get('oro_security.acl_helper');
        $defaultWarehouseChannelGroupLink = $this->manager
            ->getRepository(WarehouseChannelGroupLink::class)
            ->findSystemLink($aclHelper);

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

        $addtionalSalesChannelGroups = [
            $this->getReference(LoadSalesChannelGroupData::CHANNELGROUP_1_REF),
            $this->getReference(LoadSalesChannelGroupData::CHANNELGROUP_2_REF),
            $this->getReference(LoadSalesChannelGroupData::CHANNELGROUP_3_REF),
            $this->getReference(LoadSalesChannelGroupData::CHANNELGROUP_4_REF),
        ];

        foreach ($addtionalSalesChannelGroups as $group) {
            $defaultWarehouseChannelGroupLink->addSalesChannelGroup($group);
        }

        $this->manager->persist($defaultWarehouseChannelGroupLink);
        $this->manager->flush();
    }
}
