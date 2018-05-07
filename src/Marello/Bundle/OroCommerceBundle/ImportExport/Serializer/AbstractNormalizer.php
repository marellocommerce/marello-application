<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Serializer;

use Marello\Bundle\OroCommerceBundle\Integration\OroCommerceChannelType;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\EntityBundle\ORM\Registry;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\NormalizerInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

abstract class AbstractNormalizer implements NormalizerInterface
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }


    /**
     * @param int $id
     * @return null|Channel
     */
    protected function getIntegrationChannel($id)
    {
        /** @var Channel $channel */
        $channel = $this->registry
            ->getManagerForClass(Channel::class)
            ->getRepository(Channel::class)
            ->find($id);
        if ($channel && $channel->getType() === OroCommerceChannelType::TYPE && $channel->isEnabled()) {
            return $channel;
        }

        return null;
    }
    
    /**
     * @param Channel $channel
     * @return null|SalesChannel
     */
    protected function getSalesChannel(Channel $channel)
    {
        return $this->registry
            ->getManagerForClass(SalesChannel::class)
            ->getRepository(SalesChannel::class)
            ->findOneBy(['integrationChannel' => $channel]);
    }
}
