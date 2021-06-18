<?php

namespace Marello\Bundle\ReturnBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

use Marello\Bundle\ReturnBundle\Entity\ReturnItem;

class UpdateCurrentReturnItemsWithOrganization extends AbstractFixture
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
        $this->updateCurrentReturnItems();
    }

    /**
     * update current ReturnItem with organization
     */
    public function updateCurrentReturnItems()
    {
        $returnItems = $this->manager
            ->getRepository(ReturnItem::class)
            ->findBy(['organization' => null]);

        /** @var ReturnItem $returnItem */
        foreach ($returnItems as $returnItem) {
            $organization = $returnItem->getReturn()->getOrganization();
            $returnItem->setOrganization($organization);
            $this->manager->persist($returnItem);
        }

        $this->manager->flush();
    }
}
