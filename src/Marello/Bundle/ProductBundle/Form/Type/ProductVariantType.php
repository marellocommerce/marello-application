<?php

namespace Marello\Bundle\ProductBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Marello\Bundle\ProductBundle\Form\EventListener\VariantSubscriber;

class ProductVariantType extends AbstractType
{
    const NAME = 'marello_product_variant_form';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'variantCode',
            'text',
            [
                'required' => true,
                'label'    => 'marello.product.variant.variant_code.label',
            ]
        )
        ->add(
            'products',
            'marello_product_collection'
        );
        $builder->addEventSubscriber(new VariantSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array('data_class' => 'Marello\\Bundle\\ProductBundle\\Entity\\Variant',
                  'intention' => 'variant',
                  'single_form' => false,
            )
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
