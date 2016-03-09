<?php

namespace Marello\Bundle\PricingBundle\Form\EventListener;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Util\ProductHelper;
use Marello\Bundle\PricingBundle\Model\PricingAwareInterface;

class ChannelPricingSubscriber implements EventSubscriberInterface
{
    /** @var EntityManager $em */
    protected $em;

    /** @var string $interface  */
    protected $interface;

    /** @var Product $helper  */
    protected $helper;

    /**
     * DefaultChannelPricingSubscriber constructor.
     * @param EntityManager $em
     * @param string $interface
     * @param ProductHelper $helper
     */
    public function __construct(EntityManager $em, $interface, ProductHelper $helper)
    {
        $this->em = $em;
        $this->interface = $interface;
        $this->helper = $helper;
    }

    /**
     * Get subscribed events
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA    => 'addChannelPricingData',
            FormEvents::POST_SUBMIT     => 'handleEnabledState'
        ];
    }

    /**
     *
     * @param FormEvent $event
     */
    public function addChannelPricingData(FormEvent $event)
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

        $channels = $this->helper->getExcludedSalesChannelsIds($product);
        $form->add(
            'channelPrices',
            'marello_product_channel_price_collection',
            [
                'options' => [
                    'excluded_channels' => $channels
                ]
            ]
        );

        $event->setData($product);
    }

    /**
     * @param FormEvent $event
     */
    public function handleEnabledState(FormEvent $event)
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
    protected function clearChannelPricingCollection($product)
    {
        if (count($product->getChannelPrices()) > 0) {
            foreach ($product->getChannelPrices() as $_price) {
                $product->removeChannelPrice($_price);
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
}
