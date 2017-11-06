<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Marello\Bundle\InventoryBundle\Migrations\Data\ORM\CreateDefaultWarehouseChannelGroupLink;

class RemoveDefaultWarehouseChannelGroupLink extends AbstractFixture implements DependentFixtureInterface
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
            CreateDefaultWarehouseChannelGroupLink::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->removeDefaultWarehouseChannelGroupLink();
    }

    public function removeDefaultWarehouseChannelGroupLink()
    {
        $defaultSystemLink = $this->manager
            ->getRepository(WarehouseChannelGroupLink::class)
            ->findOneBy(['system' => true]);

        $this->manager->remove($defaultSystemLink);
        $this->manager->flush();
    }
}
