<?php

namespace Marello\Bundle\ProductBundle\Form\Type;

use Marello\Bundle\ProductBundle\Entity\Variant;
use Marello\Bundle\ProductBundle\Form\EventListener\VariantSubscriber;
use Oro\Bundle\FormBundle\Form\Type\EntityIdentifierType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class ProductVariantType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_product_variant_form';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('variantCode', HiddenType::class)
            ->add(
                'addVariants',
                EntityIdentifierType::class,
                [
                    'class'    => 'MarelloProductBundle:Product',
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true,
                ]
            )
            ->add(
                'removeVariants',
                EntityIdentifierType::class,
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
            'data_class'         => Variant::class,
            'intention'          => 'variant',
            'single_form'        => false,
            'constraints'        => [new Valid()],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
