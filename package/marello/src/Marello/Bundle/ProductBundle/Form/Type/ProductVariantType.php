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
        $builder
            ->add('variantCode', 'hidden')
            ->add(
                'addVariants',
                'oro_entity_identifier',
                [
                    'class'    => 'MarelloProductBundle:Product',
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true,
                ]
            )
            ->add(
                'removeVariants',
                'oro_entity_identifier',
                [
                    'class'    => 'MarelloProductBundle:Product',
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true,
                ]
            );

        $builder->addEventSubscriber(new VariantSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => 'Marello\Bundle\ProductBundle\Entity\Variant',
            'intention'          => 'variant',
            'single_form'        => false,
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
