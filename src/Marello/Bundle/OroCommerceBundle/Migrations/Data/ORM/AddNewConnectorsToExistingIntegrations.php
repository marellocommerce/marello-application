<?php

namespace Marello\Bundle\OroCommerceBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\OroCommerceBundle\Integration\Connector\OroCommerceCustomerConnector;
use Marello\Bundle\OroCommerceBundle\Integration\Connector\OroCommercePaymentTermConnector;
use Marello\Bundle\OroCommerceBundle\Integration\OroCommerceChannelType;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

class AddNewConnectorsToExistingIntegrations extends AbstractFixture
{
    const NEW_CONNECTORS = [OroCommerceCustomerConnector::TYPE, OroCommercePaymentTermConnector::TYPE];

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
        /** @var Channel $channel */
        $channels = $this->manager
            ->getRepository(Channel::class)
            ->findByType(OroCommerceChannelType::TYPE);
        foreach ($channels as $channel) {
            $connectors = $channel->getConnectors();
            foreach (self::NEW_CONNECTORS as $newConnector) {
                if (!in_array($newConnector, $connectors)) {
                    $connectors[] = $newConnector;
                }
            }
            $channel->setConnectors($connectors);
            $this->manager->persist($channel);
        }
        $this->manager->flush();
    }
}
