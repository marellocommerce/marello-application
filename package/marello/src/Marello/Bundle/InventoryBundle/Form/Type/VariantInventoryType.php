<?php

namespace Marello\Bundle\InventoryBundle\Form\Type;

use Marello\Bundle\ProductBundle\Entity\Variant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VariantInventoryType extends AbstractType
{
    const NAME = 'marello_variant_inventory';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('products', CollectionType::class, [
            'type'               => ProductInventoryType::NAME,
            'allow_add'          => false,
            'allow_delete'       => false,
            'cascade_validation' => true,
            'options'            => [
                'include_variants' => false,
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => Variant::class,
            'cascade_validation' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }
}
