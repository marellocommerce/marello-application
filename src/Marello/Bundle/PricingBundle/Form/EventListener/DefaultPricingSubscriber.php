<?php

namespace Marello\Bundle\PricingBundle\Form\EventListener;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Marello\Bundle\PricingBundle\Model\PricingAwareInterface;
use Marello\Bundle\PricingBundle\Entity\ProductPrice;
use Marello\Bundle\SalesBundle\Model\SalesChannelAwareInterface;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
class DefaultPricingSubscriber implements EventSubscriberInterface
{
    /** @var EntityManager $em */
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Get subscribed events
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA    => 'addPricingData',
        ];
    }

    /**
     * Preset data for pricing
     * @param FormEvent $event
     */
    public function addPricingData(FormEvent $event)
    {
        $entity = $event->getData();
        $form   = $event->getForm();

        if ($entity instanceof PricingAwareInterface && $entity instanceof SalesChannelAwareInterface) {
            if ($entity->hasPrices()) {
                $allCurrencies = $this->getCurrencies($entity->getChannels());
                $existingCurrencies = [];
                $entity
                    ->getPrices()
                    ->map(function (ProductPrice $price) use (&$existingCurrencies) {
                        $existingCurrencies[] = $price->getCurrency();
                    });

                $currencies = array_diff($allCurrencies, $existingCurrencies);

                if (!empty($currencies)) {
                    //only add prices for currencies which have not been added yet!
                    foreach ($currencies as $currency) {
                        $price = new ProductPrice();
                        $price->setCurrency($currency);
                        $price->setValue(0);
                        $entity->addPrice($price);
                    }
                    $event->setData($entity);
                }

            }
        }

        $form->add(
            'prices',
            'marello_product_price_collection'
        );
    }

    /**
     * Get available currencies for all sales channels
     * @param $channels
     * @return array
     */
    private function getCurrencies($channels)
    {
        $currencies = [];
        $channels
            ->map(function (SalesChannel $channel) use (&$currencies) {
                $currencies[] = $channel->getCurrency();
            });

        return array_unique($currencies);
    }
}
