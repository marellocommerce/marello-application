<?php

namespace Marello\Bundle\OroCommerceBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\IntegrationBundle\Entity\Channel;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\OroCommerceBundle\Entity\OroCommerceSettings;
use Marello\Bundle\OroCommerceBundle\Integration\OroCommerceChannelType;

class UpdateSalesChannelGroupExistingIntegrations extends AbstractFixture
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
        $qb = $this->manager
            ->getRepository(SalesChannel::class)
            ->createQueryBuilder('sc');
        $query = $qb
            ->where($qb->expr()->isNotNull('sc.integrationChannel'))
            ->getQuery();
        $salesChannels = $query->getResult();
        /** @var SalesChannel $salesChannel */
        foreach ($salesChannels as $salesChannel) {
            /** @var Channel $integrationChannel */
            $integrationChannel = $salesChannel->getIntegrationChannel();
            if ($integrationChannel->getType() === OroCommerceChannelType::TYPE) {
                /** @var OroCommerceSettings $transport */
                $transport = $integrationChannel->getTransport();
                if (!$transport->getSalesChannelGroup()) {
                    $transport->setSalesChannelGroup($salesChannel->getGroup());
                    $this->manager->persist($transport);
                }
            }
        }

        $this->manager->flush();
    }
}
