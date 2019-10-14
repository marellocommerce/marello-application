<?php

namespace Marello\Bundle\SubscriptionBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Marello\Bundle\SubscriptionBundle\Entity\SubscriptionItem;

class UpdateCurrentSubscriptionItemsWithOrganization extends AbstractFixture
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
        $this->updateCurrentSubscriptionItems();
    }

    /**
     * update current SubscriptionItem with organization
     */
    public function updateCurrentSubscriptionItems()
    {
        $subscriptionItems = $this->manager
            ->getRepository(SubscriptionItem::class)
            ->findBy(['organization' => null]);

        /** @var SubscriptionItem $subscriptionItem */
        foreach ($subscriptionItems as $subscriptionItem) {
            $organization = $subscriptionItem->getSubscription()->getOrganization();
            $subscriptionItem->setOrganization($organization);
            $this->manager->persist($subscriptionItem);
        }

        $this->manager->flush();
    }
}
