<?php

namespace Marello\Bundle\PricingBundle\Form\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Marello\Bundle\PricingBundle\Entity\ProductPrice;
use Marello\Bundle\PricingBundle\Provider\CurrencyProvider;
use Marello\Bundle\PricingBundle\Model\PricingAwareInterface;
use Marello\Bundle\SalesBundle\Model\SalesChannelsAwareInterface;
use Marello\Bundle\PricingBundle\Entity\AssembledPriceList;
use Marello\Bundle\PricingBundle\Entity\PriceType;
use Marello\Bundle\PricingBundle\Form\Type\AssembledPriceListCollectionType;
use Marello\Bundle\PricingBundle\Model\PriceTypeInterface;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;

class PricingSubscriber implements EventSubscriberInterface
{
    /**
     * @var PriceType[]
     */
    protected $priceTypes = [];

    public function __construct(
        protected CurrencyProvider $provider,
        protected ManagerRegistry $doctrine
    ) {
    }

    /**
     * Get subscribed events
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA    => 'preSetData',
            FormEvents::POST_SUBMIT     => 'postSubmit'
        ];
    }

    /**
     * Preset data for pricing
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $entity = $event->getData();
        $form   = $event->getForm();

        if ($entity instanceof PricingAwareInterface && $entity instanceof SalesChannelsAwareInterface) {
            $currencies = $this->provider->getCurrencies($entity->getChannels());
            if ($entity->hasPrices()) {
                $existingCurrencies = [];
                $entity
                    ->getPrices()
                    ->map(function (AssembledPriceList $price) use (&$existingCurrencies) {
                        $existingCurrencies[] = $price->getDefaultPrice()->getCurrency();
                    });

                $currencies = array_diff($currencies, $existingCurrencies);
            }

            if (!empty($currencies)) {
                //only add prices for currencies which have not been added yet!
                foreach ($currencies as $currency) {
                    $defaultPrice = new ProductPrice();
                    $defaultPrice
                        ->setCurrency($currency)
                        ->setValue(0);
                    $assembledPriceList = new AssembledPriceList();
                    $assembledPriceList
                        ->setCurrency($currency)
                        ->setDefaultPrice($defaultPrice);
                    $entity->addPrice($assembledPriceList);
                }
                $event->setData($entity);
            }
        }

        $form->add(
            'prices',
            AssembledPriceListCollectionType::class
        );
    }

    /**
     * @param FormEvent $event
     */
    public function postSubmit(FormEvent $event)
    {
        /** @var Product $product */
        $product = $event->getData();
        $form = $event->getForm();
        
        foreach ($product->getPrices() as $assembledPriceList) {
            $assembledPriceList->getDefaultPrice()
                ->setType($this->getPriceType(PriceTypeInterface::DEFAULT_PRICE))
                ->setCurrency($assembledPriceList->getCurrency());

            /** Restores currency in case if product price exists */
            if ($assembledPriceList->getSpecialPrice() !== null) {
                $assembledPriceList->getSpecialPrice()
                    ->setType($this->getPriceType(PriceTypeInterface::SPECIAL_PRICE))
                    ->setCurrency($assembledPriceList->getCurrency());
            }

            /** Removes product price from price list in case if user clear its value */
            if ($assembledPriceList->getSpecialPrice() !== null &&
                $assembledPriceList->getSpecialPrice()->getValue() === null) {
                $assembledPriceList->setSpecialPrice(null);
            }

            /** Restores currency in case if product price exists */
            if ($assembledPriceList->getMsrpPrice() !== null) {
                $assembledPriceList->getMsrpPrice()
                    ->setType($this->getPriceType(PriceTypeInterface::MSRP_PRICE))
                    ->setCurrency($assembledPriceList->getCurrency());
            }

            /** Removes product price from price list in case if user clear its value */
            if ($assembledPriceList->getMsrpPrice() !== null &&
                $assembledPriceList->getMsrpPrice()->getValue() === null) {
                $assembledPriceList->setMsrpPrice(null);
            }
        }
        
        if ($form->has('removeSalesChannels')) {
            /** @var SalesChannel[] $data */
            $data = $form->get('removeSalesChannels')->getData();
            if (empty($data)) {
                return;
            }
            $removedChannelIds = [];
            foreach ($data as $sc) {
                $removedChannelIds[] = $sc->getId();
            }
            // available currencies
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
                ->map(function (AssembledPriceList $price) use (&$currencies, &$removedPrices) {
                    if (in_array($price->getDefaultPrice()->getCurrency(), $currencies)) {
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

    /**
     * @param $name
     * @return PriceType
     */
    private function getPriceType($name)
    {
        if (!isset($this->priceTypes[$name])) {
            $this->priceTypes[$name] = $this->doctrine
                ->getManagerForClass(PriceType::class)
                ->getRepository(PriceType::class)
                ->find($name);
        }
        
        return $this->priceTypes[$name];
    }
}
