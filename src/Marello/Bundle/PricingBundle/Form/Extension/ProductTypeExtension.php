<?php

namespace Marello\Bundle\PricingBundle\Form\Extension;

use Marello\Bundle\PricingBundle\Model\PricingAwareInterface;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Form\Type\ProductType;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class ProductTypeExtension extends AbstractTypeExtension
{
    /** @var LocaleSettings $localeSettings */
    protected $localeSettings;

    /** @var string $awareInterface  */
    protected $interface;

    /**
     * PricingTypeExtension constructor.
     * @param LocaleSettings $localeSettings
     * @param string $interface
     */
    public function __construct(LocaleSettings $localeSettings, $interface)
    {
        $this->localeSettings = $localeSettings;
        $this->interface = $interface;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return ProductType::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'loadPricingSettings']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'handleEnabledState']);
    }

    /**
     * Preset data for channels
     * @param FormEvent $event
     */
    public function loadPricingSettings(FormEvent $event)
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
                'label' => 'marello.pricing.productprice.form.pricing_enabled.label',
                'mapped' => false,
                'required' => false,
                'data' => $pricingEnabled,
            ]
        );


        $this->addPricingCollection($form, $product, $event);
    }

    /**
     * @param $form
     * @param $product
     * @param $event
     */
    public function addPricingCollection($form, $product, $event)
    {
        $form->add(
            'prices',
            'marello_product_price_collection'
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
            $this->clearPricingCollection($product);
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
     * Get default currency for application
     * @return string
     */
    public function getDefaultCurrency()
    {
        return $this->localeSettings->getCurrency();
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
     * Remove prices from the collection on product
     * @param Product $product
     */
    protected function clearPricingCollection($product)
    {
        if (count($product->getPrices()) > 0) {
            foreach ($product->getPrices() as $_price) {
                $product->removePrice($_price);
            }
        }
    }
}
