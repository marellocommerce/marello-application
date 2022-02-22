<?php

namespace Marello\Bundle\PackingBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

use Marello\Bundle\PackingBundle\Entity\PackingSlipItem;

class UpdateCurrentPackingSlipItemsWithOrganization extends AbstractFixture
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
        $this->updateCurrentPackingSlipItems();
    }

    /**
     * update current PackingSlipItems with organization
     */
    public function updateCurrentPackingSlipItems()
    {
        $packingSlipItems = $this->manager
            ->getRepository(PackingSlipItem::class)
            ->findBy(['organization' => null]);

        /** @var PackingSlipItem $packingSlipItem */
        foreach ($packingSlipItems as $packingSlipItem) {
            $organization = $packingSlipItem->getPackingSlip()->getOrganization();
            $packingSlipItem->setOrganization($organization);
            $this->manager->persist($packingSlipItem);
        }
        $this->manager->flush();
    }
}
