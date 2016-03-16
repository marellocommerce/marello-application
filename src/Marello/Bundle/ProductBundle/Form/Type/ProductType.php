<?php

namespace Marello\Bundle\ProductBundle\Form\Type;

use Marello\Bundle\SalesBundle\Form\EventListener\DefaultSalesChannelSubscriber;
use Marello\Bundle\PricingBundle\Form\EventListener\PricingSubscriber;
use Marello\Bundle\PricingBundle\Form\EventListener\ChannelPricingSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    const NAME = 'marello_product_form';

    /** @var DefaultSalesChannelSubscriber $defaultSalesChannelSubscriber*/
    protected $defaultSalesChannelSubscriber;

    /** @var PricingSubscriber $pricingSubscriber */
    protected $pricingSubscriber;

    /** @var ChannelPricingSubscriber $channelPricingSubscriber */
    protected $channelPricingSubscriber;

    /**
     * ProductType constructor.
     * @param DefaultSalesChannelSubscriber $defaultSalesChannelSubscriber
     * @param PricingSubscriber $pricingSubscriber
     * @param ChannelPricingSubscriber $channelPricingSubscriber
     */
    public function __construct(
        DefaultSalesChannelSubscriber $defaultSalesChannelSubscriber,
        PricingSubscriber $pricingSubscriber,
        ChannelPricingSubscriber $channelPricingSubscriber
    ) {
        $this->defaultSalesChannelSubscriber    = $defaultSalesChannelSubscriber;
        $this->pricingSubscriber         = $pricingSubscriber;
        $this->channelPricingSubscriber  = $channelPricingSubscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                'text',
                [
                    'required' => true,
                    'label'    => 'marello.product.name.label',
                ]
            )
            ->add(
                'sku',
                'text',
                [
                    'required' => true,
                    'label'    => 'marello.product.sku.label',
                ]
            )
            ->add(
                'status',
                'entity',
                [
                    'label'    => 'marello.product.status.label',
                    'class'    => 'MarelloProductBundle:ProductStatus',
                    'property' => 'label',
                    'required' => true,
                ]
            )
            ->add(
                'inventoryItems',
                'marello_inventory_item_collection',
                [
                    'label'              => 'marello.inventory.label',
                    'cascade_validation' => true,
                ]
            )
            ->add(
                'addSalesChannels',
                'oro_entity_identifier',
                [
                    'class'    => 'MarelloSalesBundle:SalesChannel',
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true,
                ]
            )
            ->add(
                'removeSalesChannels',
                'oro_entity_identifier',
                [
                    'class'    => 'MarelloSalesBundle:SalesChannel',
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true,
                ]
            );

        $builder->addEventSubscriber($this->defaultSalesChannelSubscriber);
        $builder->addEventSubscriber($this->pricingSubscriber);
        $builder->addEventSubscriber($this->channelPricingSubscriber);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => 'Marello\Bundle\ProductBundle\Entity\Product',
            'intention'          => 'product',
            'single_form'        => true,
            'cascade_validation' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
