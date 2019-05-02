<?php

namespace Marello\Bundle\ProductBundle\Form\Type;

use Marello\Bundle\ProductBundle\Entity\ProductSupplierRelation;
use Marello\Bundle\SupplierBundle\Form\Type\SupplierSelectType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class ProductSupplierRelationType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_product_supplier_relation_form';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'supplier',
                SupplierSelectType::class,
                [
                    'required'       => true,
                    'label'          => 'marello.supplier.entity_label',
                    'create_enabled' => false,
                ]
            )
            ->add('quantityOfUnit')
            ->add('priority')
            ->add('cost')
            ->add('canDropship')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => ProductSupplierRelation::class,
            'constraints'        => [new Valid()],
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
