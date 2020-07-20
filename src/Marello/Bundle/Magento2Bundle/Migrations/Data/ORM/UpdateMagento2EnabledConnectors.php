<?php

namespace Marello\Bundle\Magento2Bundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\Magento2Bundle\Entity\Magento2Transport;
use Marello\Bundle\Magento2Bundle\Integration\Connector\InitialOrderConnector;
use Marello\Bundle\Magento2Bundle\Integration\Connector\OrderConnector;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\MigrationBundle\Fixture\VersionedFixtureInterface;

class UpdateMagento2EnabledConnectors extends AbstractFixture implements VersionedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function getVersion()
    {
        return '1.0';
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var Magento2Transport[] $magento2Transport */
        $magento2Integrations = $manager->getRepository(Integration::class)->findBy(
            [
                'type' => 'magento2'
            ]
        );
        foreach ($magento2Integrations as $magento2Integration) {
            $magento2Integration->setConnectors(
                \array_unique(
                    \array_merge(
                        $magento2Integration->getConnectors(),
                        [
                            OrderConnector::TYPE,
                            InitialOrderConnector::TYPE
                        ]
                    )
                )
            );
        }

        $manager->flush();
    }
}
