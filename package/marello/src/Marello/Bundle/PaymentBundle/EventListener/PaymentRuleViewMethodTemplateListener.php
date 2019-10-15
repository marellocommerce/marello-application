<?php

namespace Marello\Bundle\PaymentBundle\EventListener;

use Marello\Bundle\PaymentBundle\Event\PaymentMethodConfigDataEvent;
use Marello\Bundle\PaymentBundle\Method\Provider\PaymentMethodProviderInterface;

class PaymentRuleViewMethodTemplateListener
{
    /**
     * @var string
     */
    private $template;

    /**
     * @var PaymentMethodProviderInterface
     */
    private $provider;

    /**
     * @param string                          $template
     * @param PaymentMethodProviderInterface $provider
     */
    public function __construct($template, PaymentMethodProviderInterface $provider)
    {
        $this->template = $template;
        $this->provider = $provider;
    }

    /**
     * @param PaymentMethodConfigDataEvent $event
     */
    public function onGetConfigData(PaymentMethodConfigDataEvent $event)
    {
        if ($this->provider->hasPaymentMethod($event->getMethodIdentifier())) {
            $event->setTemplate($this->template);
        }
    }
}
