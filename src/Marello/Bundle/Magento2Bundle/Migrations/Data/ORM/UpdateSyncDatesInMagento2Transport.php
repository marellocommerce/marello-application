<?php

namespace Marello\Bundle\Magento2Bundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\Magento2Bundle\Entity\Magento2Transport;

class UpdateSyncDatesInMagento2Transport extends AbstractFixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var Magento2Transport[] $magento2Transport */
        $magento2Transports = $manager->getRepository(Magento2Transport::class)->findAll();
        foreach ($magento2Transports as $magento2Transport) {
            $magento2Transport->setSyncStartDate(
                new \DateTime('now', new \DateTimeZone('UTC'))
            );
            $magento2Transport->setInitialSyncStartDate(
                new \DateTime('2007-01-01', new \DateTimeZone('UTC'))
            );
        }

        $manager->flush();
    }
}
