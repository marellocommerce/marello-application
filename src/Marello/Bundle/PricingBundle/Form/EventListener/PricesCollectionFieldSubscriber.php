<?php

namespace Marello\Bundle\PricingBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;

use Marello\Bundle\PricingBundle\Entity\ProductPrice;
use Marello\Bundle\ProductBundle\Entity\Product;

class PricesCollectionFieldSubscriber implements EventSubscriberInterface
{
    /** @var LocaleSettings $localeSettings */
    protected $localeSettings;

    /**
     * @param LocaleSettings $localeSettings
     */
    public function __construct(LocaleSettings $localeSettings)
    {
        $this->localeSettings = $localeSettings;
    }
    /**
     * Get subscribed events
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(FormEvents::PRE_SET_DATA => 'preSetData');
    }

    /**
     * Preset data for channels
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $entity = $event->getData();
        $form = $event->getForm();
        if (!$entity || null === $entity->getId()) {
            if($form->has('channels')) {
                if($entity instanceof Product) {
                    if(count($entity->getChannels()) > 0) {
                        foreach($entity->getChannels() as $_channel) {
                            $default = new ProductPrice();
                            $default->setChannel($_channel);
                            $default->setCurrency($this->getDefaultCurrency());
                            $entity->addPrice($default);
                        }
                        $event->setData($entity);
                    }
                }
            }
        }
    }

    /**
     * Get default currency for application
     * @return string
     */
    public function getDefaultCurrency()
    {
        return $this->localeSettings->getCurrency();
    }
}
