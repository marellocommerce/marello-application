<?php

namespace Marello\Bundle\PaymentBundle\Method\EventListener;

use Oro\Bundle\IntegrationBundle\Event\Action\ChannelDisableEvent;
use Oro\Bundle\IntegrationBundle\Generator\IntegrationIdentifierGeneratorInterface;
use Marello\Bundle\PaymentBundle\Method\Handler\PaymentMethodDisableHandlerInterface;

class PaymentMethodDisableIntegrationListener
{
    /**
     * @var IntegrationIdentifierGeneratorInterface
     */
    private $methodIdentifierGenerator;

    /**
     * @var PaymentMethodDisableHandlerInterface
     */
    private $paymentMethodDisableHandler;

    /**
     * @param string                                  $channelType
     * @param IntegrationIdentifierGeneratorInterface $methodIdentifierGenerator
     * @param PaymentMethodDisableHandlerInterface    $paymentMethodDisableHandler
     */
    public function __construct(
        $channelType,
        IntegrationIdentifierGeneratorInterface $methodIdentifierGenerator,
        PaymentMethodDisableHandlerInterface $paymentMethodDisableHandler
    ) {
        $this->channelType = $channelType;
        $this->methodIdentifierGenerator = $methodIdentifierGenerator;
        $this->paymentMethodDisableHandler = $paymentMethodDisableHandler;
    }

    /**
     * @param ChannelDisableEvent $event
     */
    public function onIntegrationDisable(ChannelDisableEvent $event)
    {
        $channel = $event->getChannel();
        $channelType = $channel->getType();
        if ($channelType === $this->channelType) {
            $methodId = $this->methodIdentifierGenerator->generateIdentifier($channel);
            $this->paymentMethodDisableHandler->handleMethodDisable($methodId);
        }
    }
}
