<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderItem;

class UpdateCurrentReplenismentItemsWithOrganization extends AbstractFixture
{
    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->updateCurrentReplenishmentOrderItems();
    }

    /**
     * update current ReplenishmentOrderItem with organization
     */
    public function updateCurrentReplenishmentOrderItems()
    {
        $replenishmentOrderItems = $this->manager
            ->getRepository(ReplenishmentOrderItem::class)
            ->findBy(['organization' => null]);

        /** @var ReplenishmentOrderItem $replenishmentOrderItem */
        foreach ($replenishmentOrderItems as $replenishmentOrderItem) {
            $organization = $replenishmentOrderItem->getOrder()->getOrganization();
            $replenishmentOrderItem->setOrganization($organization);
            $this->manager->persist($replenishmentOrderItem);
        }

        $this->manager->flush();
    }
}
