<?php

namespace Marello\Bundle\OrderBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

use Marello\Bundle\OrderBundle\Entity\OrderItem;

class UpdateCurrentOrderItemsWithOrganization extends AbstractFixture
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
        $this->updateCurrentOrderItems();
    }

    /**
     * update current OrderItems with organization
     */
    public function updateCurrentOrderItems()
    {
        $orderItems = $this->manager
            ->getRepository(OrderItem::class)
            ->findBy(['organization' => null]);

        /** @var OrderItem $orderItem */
        foreach ($orderItems as $orderItem) {
            $organization = $orderItem->getOrder()->getOrganization();
            $orderItem->setOrganization($organization);
            $this->manager->persist($orderItem);
        }
        $this->manager->flush();
    }
}
