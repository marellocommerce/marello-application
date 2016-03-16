<?php

namespace Marello\Bundle\PricingBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Marello\Bundle\PricingBundle\Entity\ProductPrice;
use Marello\Bundle\PricingBundle\Provider\CurrencyProvider;
use Marello\Bundle\PricingBundle\Model\PricingAwareInterface;
use Marello\Bundle\SalesBundle\Model\SalesChannelAwareInterface;

class PricingSubscriber implements EventSubscriberInterface
{
    /** @var CurrencyProvider $provider */
    protected $provider;

    public function __construct(CurrencyProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Get subscribed events
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA    => 'addPricingData',
            FormEvents::POST_SUBMIT     => 'handlePricingData'
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
            $currencies = $this->provider->getCurrencies($entity->getChannels());
            if ($entity->hasPrices()) {
                $existingCurrencies = [];
                $entity
                    ->getPrices()
                    ->map(function (ProductPrice $price) use (&$existingCurrencies) {
                        $existingCurrencies[] = $price->getCurrency();
                    });

                $currencies = array_diff($currencies, $existingCurrencies);
            }

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

        $form->add(
            'prices',
            'marello_product_price_collection'
        );
    }

    /**
     * @param FormEvent $event
     */
    public function handlePricingData(FormEvent $event)
    {
        /** @var Product $product */
        $product = $event->getData();
        $form = $event->getForm();
        if ($form->has('removeSalesChannels')) {
            $data = $form->get('removeSalesChannels')->getData();
            if (empty($data)) {
                return;
            }
            $removedChannelIds = [];
            foreach ($data as $sc) {
                $removedChannelIds[] = $sc->getId();
            }
            // currencies which should be removed
            $currencies = $this->provider->getCurrencies($data);

            // unset currency for channels which still holds a currency
            // and is not removed..
            foreach ($product->getChannels() as $channel) {
                if (in_array($channel->getCurrency(), $currencies)
                    && !in_array($channel->getId(), $removedChannelIds)) {
                    unset($currencies[$channel->getCurrency()]);
                }
            }

            $removedPrices = [];
            // get prices which should be removed based on the currencies left
            $product
                ->getPrices()
                ->map(function (ProductPrice $price) use (&$currencies, &$removedPrices) {
                    if (in_array($price->getCurrency(), $currencies)) {
                        $removedPrices[] = $price;
                    }
                });

            if (!empty($removedPrices)) {
                //only remove prices for currencies which have been deleted!
                foreach ($removedPrices as $price) {
                    $product->removePrice($price);
                }
            }
        }

        $event->setData($product);
    }
}
