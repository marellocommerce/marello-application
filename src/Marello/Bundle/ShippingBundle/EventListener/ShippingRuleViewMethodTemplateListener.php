<?php

namespace Marello\Bundle\ShippingBundle\EventListener;

use Marello\Bundle\ShippingBundle\Event\ShippingMethodConfigDataEvent;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodProviderInterface;

class ShippingRuleViewMethodTemplateListener
{
    /**
     * @var string
     */
    private $template;

    /**
     * @var ShippingMethodProviderInterface
     */
    private $provider;

    /**
     * @param string                          $template
     * @param ShippingMethodProviderInterface $provider
     */
    public function __construct($template, ShippingMethodProviderInterface $provider)
    {
        $this->template = $template;
        $this->provider = $provider;
    }

    /**
     * @param ShippingMethodConfigDataEvent $event
     */
    public function onGetConfigData(ShippingMethodConfigDataEvent $event)
    {
        if ($this->provider->hasShippingMethod($event->getMethodIdentifier())) {
            $event->setTemplate($this->template);
        }
    }
}
