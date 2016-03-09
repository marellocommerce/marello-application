<?php

namespace Marello\Bundle\PricingBundle\Form\EventListener;

use Doctrine\ORM\EntityManager;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Marello\Bundle\PricingBundle\Model\PricingAwareInterface;
use Marello\Bundle\PricingBundle\Entity\ProductPrice;
use Marello\Bundle\SalesBundle\Model\SalesChannelAwareInterface;
use Marello\Bundle\PricingBundle\Model\CurrencyAwareInterface;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;

class PricingSubscriber implements EventSubscriberInterface
{
    /** @var EntityManager $em */
    protected $em;

    /** @var LocaleSettings $localeSettings */
    protected $localeSettings;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, LocaleSettings $localeSettings)
    {
        $this->em = $em;
        $this->localeSettings = $localeSettings;
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
            $currencies = $this->getCurrencies($entity->getChannels());
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
            $entities = (!empty($data)) ? $data : $product->getPrices();
            // currencies which should be removed
            $currencies = $this->getCurrencies($entities);
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
                $defaultPrice = null;
                foreach ($removedPrices as $price) {
                    if ($this->localeSettings->getCurrency() === $price->getCurrency()) {
                        $defaultPrice = $price;
                        continue;
                    }
                    $product->removePrice($price);
                }

                // check if the default price is the "last" price left in the collection
                // if not, we can remove it safely
                if (count($product->getPrices()) > 1) {
                    $product->removePrice($defaultPrice);
                }

                // if prices is still empty, add the application currency price
                if (!$product->hasPrices()) {
                    $price = new ProductPrice();
                    $price->setCurrency($this->localeSettings->getCurrency());
                    $price->setValue(0);
                    $product->addPrice($price);
                }
            }
        }

        $event->setData($product);
    }

    /**
     * Get available currencies
     * @param $entities
     * @return array
     */
    private function getCurrencies($entities)
    {
        $currencies = [];
        if (!is_array($entities)) {
            $entities
                ->map(function (CurrencyAwareInterface $entity) use (&$currencies) {
                    $currencies[] = $entity->getCurrency();
                });
        } else {
            foreach ($entities as $entity) {
                if ($entity instanceof CurrencyAwareInterface) {
                    $currencies[] = $entity->getCurrency();
                }
            }
        }

        return array_unique($currencies);
    }
}
