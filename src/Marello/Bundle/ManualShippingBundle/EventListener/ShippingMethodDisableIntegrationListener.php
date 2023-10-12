<?php

namespace Marello\Bundle\ManualShippingBundle\EventListener;

use Marello\Bundle\ManualShippingBundle\Integration\ManualShippingChannelType;
use Marello\Bundle\ShippingBundle\Method\Handler\ShippingMethodDisableHandlerInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Event\Action\ChannelDisableEvent;
use Oro\Bundle\IntegrationBundle\Generator\IntegrationIdentifierGeneratorInterface;

class ShippingMethodDisableIntegrationListener
{
    /**
     * @var IntegrationIdentifierGeneratorInterface
     */
    private $integrationIdentifierGenerator;

    /**
     * @var ShippingMethodDisableHandlerInterface
     */
    private $shippingMethodDisableHandler;

    /**
     * @param IntegrationIdentifierGeneratorInterface $integrationIdentifierGenerator
     * @param ShippingMethodDisableHandlerInterface   $shippingMethodDisableHandler
     */
    public function __construct(
        IntegrationIdentifierGeneratorInterface $integrationIdentifierGenerator,
        ShippingMethodDisableHandlerInterface $shippingMethodDisableHandler
    ) {
        $this->integrationIdentifierGenerator = $integrationIdentifierGenerator;
        $this->shippingMethodDisableHandler = $shippingMethodDisableHandler;
    }

    /**
     * @param ChannelDisableEvent $event
     */
    public function onIntegrationDisable(ChannelDisableEvent $event)
    {
        /** @var Channel $channel */
        $channel = $event->getChannel();
        $channelType = $channel->getType();
        if ($channelType === ManualShippingChannelType::TYPE) {
            $methodId = $this->integrationIdentifierGenerator->generateIdentifier($channel);
            $this->shippingMethodDisableHandler->handleMethodDisable($methodId);
        }
    }
}
