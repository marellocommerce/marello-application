<?php

namespace Marello\Bundle\PricingBundle\Form\EventListener;

use Doctrine\ORM\EntityManager;
use Marello\Bundle\PricingBundle\Entity\PriceType;
use Marello\Bundle\PricingBundle\Form\Type\AssembledChannelPriceListCollectionType;
use Marello\Bundle\PricingBundle\Model\PriceTypeInterface;
use Marello\Bundle\PricingBundle\Model\PricingAwareInterface;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Provider\ChannelProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class ChannelPricingSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $interface;

    /**
     * @var ChannelProvider
     */
    protected $provider;
    
    /**
     * @var PriceType[]
     */
    protected $priceTypes = [];

    /**
     * @param EntityManager $em
     * @param string $interface
     * @param ChannelProvider $provider
     */
    public function __construct(EntityManager $em, $interface, ChannelProvider $provider)
    {
        $this->em = $em;
        $this->interface = $interface;
        $this->provider = $provider;
    }

    /**
     * Get subscribed events
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA    => 'preSetData',
            FormEvents::PRE_SUBMIT          => 'submit',
            FormEvents::POST_SUBMIT     => 'postSubmit'
        ];
    }

    /**
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $product = $event->getData();

        if (!$this->isApplicable($product)) {
            return;
        }

        $productData = (array)$product->getData();
        $pricingEnabled = !empty($productData[PricingAwareInterface::CHANNEL_PRICING_STATE_KEY]);
        $form = $event->getForm();
        $form->add(
            PricingAwareInterface::CHANNEL_PRICING_STATE_KEY,
            'checkbox',
            [
                'label' => 'marello.pricing.productchannelprice.form.pricing_enabled.label',
                'mapped' => false,
                'required' => false,
                'data' => $pricingEnabled,
            ]
        );

        $channels = $this->provider->getExcludedSalesChannelsIds($product);
        $form->add(
            'channelPrices',
            AssembledChannelPriceListCollectionType::class,
            [
                'options' => [
                    'excluded_channels' => $channels
                ]
            ]
        );

        $event->setData($product);
    }

    /**
     *
     * @param FormEvent $event
     */
    public function submit(FormEvent $event)
    {
        $data = $event->getData();
        if (isset($data['channelPrices'])) {
            foreach ($data['channelPrices'] as $k => $channelPrice) {
                $channelPrice['defaultPrice']['channel'] = $channelPrice['channel'];
                $channelPrice['defaultPrice']['currency'] = $channelPrice['currency'];
                $channelPrice['specialPrice']['channel'] = $channelPrice['channel'];
                $channelPrice['specialPrice']['currency'] = $channelPrice['currency'];

                $data['channelPrices'][$k] = $channelPrice;
            }
            $event->setData($data);
        }
    }

    /**
     * @param FormEvent $event
     */
    public function postSubmit(FormEvent $event)
    {
        /** @var Product $product */
        $product = $event->getData();
        if (!$this->isApplicable($product)) {
            return;
        }

        $form = $event->getForm();
        if (!$form->has(PricingAwareInterface::CHANNEL_PRICING_STATE_KEY)) {
            return;
        }

        $pricingEnabled = $this->getPricingEnabled($form);
        $data = $product->getData();
        if (!$data) {
            $data = [];
        }

        foreach ($product->getChannelPrices() as $assembledChannelPriceList) {
            $assembledChannelPriceList->getDefaultPrice()
                ->setType($this->getPriceType(PriceTypeInterface::DEFAULT_PRICE))
                ->setCurrency($assembledChannelPriceList->getCurrency());
            if ($assembledChannelPriceList->getSpecialPrice() !== null &&
                $assembledChannelPriceList->getSpecialPrice()->getValue() === null) {
                $assembledChannelPriceList->setSpecialPrice(null);
            } elseif ($assembledChannelPriceList->getSpecialPrice() !== null) {
                $assembledChannelPriceList->getSpecialPrice()
                    ->setType($this->getPriceType(PriceTypeInterface::SPECIAL_PRICE))
                    ->setCurrency($assembledChannelPriceList->getCurrency());
            }
        }

        if ($form->has('removeSalesChannels') && !empty($form->get('removeSalesChannels')->getData())) {
            $removedChannels = $form->get('removeSalesChannels')->getData();
            $this->removePricesByChannels($removedChannels, $product);
            $data[PricingAwareInterface::CHANNEL_PRICING_DROP_KEY] = true;
            if (!$product->hasChannelPrices()) {
                $pricingEnabled = false;
            }
        }

        if (array_key_exists(PricingAwareInterface::CHANNEL_PRICING_STATE_KEY, $data)
            && $data[PricingAwareInterface::CHANNEL_PRICING_STATE_KEY] === $pricingEnabled
        ) {
            return;
        }

        if (!$pricingEnabled) {
            $data[PricingAwareInterface::CHANNEL_PRICING_DROP_KEY] = true;
            $this->clearChannelPricingCollection($product);
        }

        $data[PricingAwareInterface::CHANNEL_PRICING_STATE_KEY] = $pricingEnabled;

        $product->setData($data);
        $event->setData($product);
    }

    /**
     * @param FormInterface $form
     *
     * @return bool
     */
    protected function getPricingEnabled(FormInterface $form)
    {
        if (!$form->has(PricingAwareInterface::CHANNEL_PRICING_STATE_KEY)) {
            throw new \InvalidArgumentException(sprintf('%s form child is missing'));
        }

        $data = $form->get(PricingAwareInterface::CHANNEL_PRICING_STATE_KEY)->getData();

        return filter_var($data, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Remove channel prices from the collection on product
     * @param Product $product
     */
    protected function clearChannelPricingCollection(Product $product)
    {
        if (count($product->getChannelPrices()) > 0) {
            foreach ($product->getChannelPrices() as $_price) {
                $product->removeChannelPrice($_price);
            }
        }
    }

    protected function removePricesByChannels(array $channels, Product $product)
    {
        $ids = [];
        foreach ($channels as $channel) {
            $ids[] = $channel->getId();
        }

        if (count($product->getChannelPrices()) > 0) {
            foreach ($product->getChannelPrices() as $_price) {
                if (in_array($_price->getChannel()->getId(), $ids)) {
                    $product->removeChannelPrice($_price);
                }
            }
        }
    }
    /**
     * @param Product $product
     *
     * @return bool
     */
    protected function isApplicable(Product $product = null)
    {
        if (!$product) {
            return false;
        }

        return in_array($this->interface, class_implements($product), true);
    }

    /**
     * @param $name
     * @return PriceType
     */
    private function getPriceType($name)
    {
        if (!isset($this->priceTypes[$name])) {
            $this->priceTypes[$name] = $this->em
                ->getRepository(PriceType::class)
                ->find($name);
        }

        return $this->priceTypes[$name];
    }
}
