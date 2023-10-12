<?php

namespace Marello\Bundle\ShippingBundle\Method\Provider\Integration;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository;
use Marello\Bundle\ShippingBundle\Method\Factory\IntegrationShippingMethodFactoryInterface;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodInterface;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodProviderInterface;

class ChannelShippingMethodProvider implements ShippingMethodProviderInterface
{
    /**
     * @var string
     */
    private $channelType;

    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * @var IntegrationShippingMethodFactoryInterface
     */
    private $methodFactory;

    /**
     * @var ShippingMethodInterface[]
     */
    private $methods = [];

    /**
     * @var Channel[]
     */
    private $loadedChannels = [];

    /**
     * @param string                                    $channelType
     * @param DoctrineHelper                            $doctrineHelper
     * @param IntegrationShippingMethodFactoryInterface $methodFactory
     */
    public function __construct(
        $channelType,
        DoctrineHelper $doctrineHelper,
        IntegrationShippingMethodFactoryInterface $methodFactory
    ) {
        $this->channelType = $channelType;
        $this->doctrineHelper = $doctrineHelper;
        $this->methodFactory = $methodFactory;
    }

    /**
     * We need only non dirty channels for creating methods.
     * For example if entity was changed on form submit, we will have dirty channel in Unit of work.
     *
     * @param Channel            $channel
     * @param LifecycleEventArgs $event
     */
    public function postLoad(Channel $channel, LifecycleEventArgs $event)
    {
        if ($channel->getType() === $this->channelType) {
            $this->loadedChannels[] = $channel;
            $this->createMethodFromChannel($channel);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getShippingMethods()
    {
        $this->loadedChannels = $this->loadChannels();
        foreach ($this->loadedChannels as $channel) {
            $this->createMethodFromChannel($channel);
        }

        return $this->methods;
    }

    /**
     * {@inheritDoc}
     */
    public function getShippingMethod($name)
    {
        if ($this->hasShippingMethod($name)) {
            return $this->getShippingMethods()[$name];
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function hasShippingMethod($name)
    {
        return array_key_exists($name, $this->getShippingMethods());
    }

    /**
     * @param Channel $channel
     */
    private function createMethodFromChannel(Channel $channel)
    {
        $method = $this->methodFactory->create($channel);
        $this->methods[$method->getIdentifier()] = $method;
    }

    /**
     * @return ChannelRepository|\Doctrine\ORM\EntityRepository
     */
    private function getRepository()
    {
        return $this->doctrineHelper->getEntityRepository('OroIntegrationBundle:Channel');
    }

    private function loadChannels()
    {
        $qb = $this->getRepository()->createQueryBuilder('channel');
        $qb
            ->where($qb->expr()->eq('channel.type', ':type'))
            ->setParameter('type', $this->channelType);

        if (count($this->loadedChannels) > 0) {
            $qb
                ->andWhere($qb->expr()->notIn('channel', ':channels'))
                ->setParameter('channels', $this->loadedChannels);
        }

        return $qb->getQuery()->getResult();
    }
}
