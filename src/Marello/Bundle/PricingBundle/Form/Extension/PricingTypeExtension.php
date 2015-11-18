<?php

namespace Marello\Bundle\PricingBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\PricingBundle\Entity\ProductPrice;
use Marello\Bundle\ProductBundle\Form\Type\ProductType;
use Marello\Bundle\PricingBundle\Model\PricingAwareInterface;

class PricingTypeExtension extends AbstractTypeExtension
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
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'loadPricingCollection']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'handleEnabledState']);
    }

    /**
     * Preset data for channels
     * @param FormEvent $event
     */
    public function loadPricingCollection(FormEvent $event)
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
                'label' => 'marello.productprice.form.pricing_enabled.label',
                'mapped' => false,
                'required' => false,
                'data' => $pricingEnabled,
            ]
        );

//        $form = $event->getForm();
//        if (!$product || null === $product->getId()) {
//            if($form->has('channels')) {
//                if($entity instanceof Product) {
//                    if(count($entity->getChannels()) > 0) {
//                        foreach($entity->getChannels() as $_channel) {
//                            $default = new ProductPrice();
//                            $default->setChannel($_channel);
//                            $default->setCurrency($this->getDefaultCurrency());
//                            $entity->addPrice($default);
//                        }
//                        $event->setData($entity);
//                    }
//                }
//            }
//        }
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
}
