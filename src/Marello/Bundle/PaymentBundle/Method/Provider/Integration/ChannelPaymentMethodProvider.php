<?php

namespace Marello\Bundle\PaymentBundle\Method\Provider\Integration;

use Marello\Bundle\PaymentBundle\Method\Provider\PaymentMethodProviderInterface;
use Marello\Bundle\PaymentBundle\Method\Factory\IntegrationPaymentMethodFactoryInterface;
use Marello\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository;

class ChannelPaymentMethodProvider implements PaymentMethodProviderInterface
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
     * @var IntegrationPaymentMethodFactoryInterface
     */
    private $methodFactory;

    /**
     * @var PaymentMethodInterface[]
     */
    private $methods = [];

    /**
     * @var Channel[]
     */
    private $loadedChannels = [];

    /**
     * @param string                                    $channelType
     * @param DoctrineHelper                            $doctrineHelper
     * @param IntegrationPaymentMethodFactoryInterface $methodFactory
     */
    public function __construct(
        $channelType,
        DoctrineHelper $doctrineHelper,
        IntegrationPaymentMethodFactoryInterface $methodFactory
    ) {
        $this->channelType = $channelType;
        $this->doctrineHelper = $doctrineHelper;
        $this->methodFactory = $methodFactory;
    }

    /**
     * We need only non dirty channels for creating methods.
     * For example if entity was changed on form submit, we will have dirty channel in Unit of work.
     *
     * @param Channel $channel
     */
    public function postLoad(Channel $channel)
    {
        if ($channel->getType() === $this->channelType) {
            $this->loadedChannels[] = $channel;
            $this->createMethodFromChannel($channel);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getPaymentMethods()
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
    public function getPaymentMethod($name)
    {
        if ($this->hasPaymentMethod($name)) {
            return $this->getPaymentMethods()[$name];
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function hasPaymentMethod($name)
    {
        return array_key_exists($name, $this->getPaymentMethods());
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
