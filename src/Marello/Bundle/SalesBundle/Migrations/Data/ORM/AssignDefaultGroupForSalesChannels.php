<?php

namespace Marello\Bundle\SalesBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class AssignDefaultGroupForSalesChannels extends AbstractFixture implements
    DependentFixtureInterface,
    ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadSalesChannelData::class,
            LoadSalesChannelGroupData::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $salesChannels = $manager->getRepository(SalesChannel::class)->findAll();
        /** @var AclHelper $aclHelper */
        $aclHelper = $this->container->get('oro_security.acl_helper');
        $defaultSystemGroup = $manager
            ->getRepository(SalesChannelGroup::class)
            ->findSystemChannelGroup($aclHelper);
        foreach ($salesChannels as $salesChannel) {
            $salesChannel->setGroup($defaultSystemGroup);
            $manager->persist($salesChannel);
        }
        $manager->flush();
    }
}
