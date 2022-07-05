<?php

namespace Marello\Bundle\RefundBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

use Marello\Bundle\RefundBundle\Entity\RefundItem;

class UpdateCurrentRefundItemsWithOrganization extends AbstractFixture
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
        $this->updateCurrentRefundItems();
    }

    /**
     * update current RefundItem with organization
     */
    public function updateCurrentRefundItems()
    {
        $refundItems = $this->manager
            ->getRepository(RefundItem::class)
            ->findBy(['organization' => null]);

        /** @var RefundItem $refundItem */
        foreach ($refundItems as $refundItem) {
            $organization = $refundItem->getRefund()->getOrganization();
            $refundItem->setOrganization($organization);
            $this->manager->persist($refundItem);
        }

        $this->manager->flush();
    }
}
