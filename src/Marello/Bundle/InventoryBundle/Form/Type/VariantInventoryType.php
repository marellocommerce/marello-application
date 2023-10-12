<?php

namespace Marello\Bundle\InventoryBundle\Form\Type;

use Marello\Bundle\ProductBundle\Entity\Variant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class VariantInventoryType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_variant_inventory';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('products', CollectionType::class, [
            'entry_type'         => ProductInventoryType::class, // TODO: resolve missed class
            'allow_add'          => false,
            'allow_delete'       => false,
            'constraints'        => [new Valid()],
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
            'data_class'  => Variant::class,
            'constraints' => [
                new Valid()
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
