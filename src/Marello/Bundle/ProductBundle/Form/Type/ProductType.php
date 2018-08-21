<?php

namespace Marello\Bundle\ProductBundle\Form\Type;

use Marello\Bundle\CatalogBundle\Entity\Category;
use Marello\Bundle\PricingBundle\Form\EventListener\ChannelPricingSubscriber;
use Marello\Bundle\PricingBundle\Form\EventListener\PricingSubscriber;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Form\EventListener\DefaultSalesChannelSubscriber;
use Oro\Bundle\AttachmentBundle\Form\Type\ImageType;
use Oro\Bundle\FormBundle\Form\Type\EntityIdentifierType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    const NAME = 'marello_product_form';

    /**
     * @var DefaultSalesChannelSubscriber
     */
    protected $defaultSalesChannelSubscriber;

    /**
     * @var PricingSubscriber
     */
    protected $pricingSubscriber;

    /**
     * @var ChannelPricingSubscriber
     */
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
                'manufacturingCode',
                'text',
                [
                    'required' => false,
                    'label'    => 'marello.product.manufacturing_code.label',
                ]
            )
            ->add(
                'weight',
                'number',
                [
                    'required'  => false,
                    'scale'     => 2,
                    'label'     => 'marello.product.weight.label'
                ]
            )
            ->add(
                'warranty',
                'number',
                [
                    'required'  => false,
                    'label'     => 'marello.product.warranty.label',
                    'tooltip'   => 'marello.product.form.tooltip.warranty'
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
                'addSalesChannels',
                EntityIdentifierType::NAME,
                [
                    'class'    => 'MarelloSalesBundle:SalesChannel',
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true,
                ]
            )
            ->add(
                'removeSalesChannels',
                EntityIdentifierType::NAME,
                [
                    'class'    => 'MarelloSalesBundle:SalesChannel',
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true,
                ]
            )
            ->add(
                'suppliers',
                'marello_product_supplier_relation_collection_form',
                [
                    'label'              => 'marello.supplier.entity_label',
                    'cascade_validation' => true,
                ]
            )
            ->add('taxCode', 'marello_tax_taxcode_select')
            ->add(
                'salesChannelTaxCodes',
                'marello_product_channel_tax_relation_collection_form',
                [
                    'label'              => 'marello.tax.taxcode.entity_label',
                    'cascade_validation' => true,
                ]
            )
            ->add(
                'image',
                ImageType::class,
                [
                    'label' => 'marello.product.image.label',
                    'required' => false
                ]
            )
            ->add(
                'appendCategories',
                EntityIdentifierType::NAME,
                [
                    'class'    => Category::class,
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true,
                ]
            )
            ->add(
                'removeCategories',
                EntityIdentifierType::NAME,
                [
                    'class'    => Category::class,
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
            'data_class'         => Product::class,
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
