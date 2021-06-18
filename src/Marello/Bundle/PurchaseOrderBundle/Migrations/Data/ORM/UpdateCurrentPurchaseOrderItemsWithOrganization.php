<?php

namespace Marello\Bundle\PurchaseOrderBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;

class UpdateCurrentPurchaseOrderItemsWithOrganization extends AbstractFixture
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
        $this->updateCurrentPurchaseOrderItems();
    }

    /**
     * update current PurchaseOrderItem with organization
     */
    public function updateCurrentPurchaseOrderItems()
    {
        $poItems = $this->manager
            ->getRepository(PurchaseOrderItem::class)
            ->findBy(['organization' => null]);

        /** @var PurchaseOrderItem $poItem */
        foreach ($poItems as $poItem) {
            $organization = $poItem->getOrder()->getOrganization();
            $poItem->setOrganization($organization);
            $this->manager->persist($poItem);
        }

        $this->manager->flush();
    }
}
