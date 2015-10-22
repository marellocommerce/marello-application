<?php

namespace Marello\Bundle\ProductBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ProductType extends AbstractType
{
    const NAME = 'marello_product_form';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            'text',
            [
                'required' => true,
                'label'    => 'marello.product.name.label'
            ]
        )
        ->add(
            'sku',
            'text',
            [
                'required' => true,
                'label'    => 'marello.product.sku.label'
            ]
        )
        ->add(
            'price',
            'oro_money',
            [
                'required' => true,
                'label' => 'marello.product.price.label'
            ]
        )
        ->add(
            'status',
            'entity',
            array(
                'label' => 'marello.product.status.label',
                'class' => 'MarelloProductBundle:ProductStatus',
                'property' => 'label',
                'required' => true,
            )
        )
        ->add(
            'stockLevel',
            'text',
            [
                'required' => true,
                'label'    => 'marello.product.stock_level.label',
                'read_only'    => false
            ]
        )
        ->add('prices',
            'marello_product_price_collection'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array('data_class' => 'Marello\\Bundle\\ProductBundle\\Entity\\Product',
                  'intention' => 'product',
                  'single_form' => false)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
