<?php

namespace Marello\Bundle\InventoryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
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
        $builder->add('products', 'collection', [
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
            'data_class'         => 'Marello\Bundle\ProductBundle\Entity\Variant',
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
}
