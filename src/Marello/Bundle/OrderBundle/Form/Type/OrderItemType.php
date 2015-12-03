<?php

namespace Marello\Bundle\OrderBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderItemType extends AbstractType
{
    const NAME = 'marello_order_item';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'product',
                'marello_product_select',
                [
                    'required'       => true,
                    'label'          => 'marello.product.entity_label',
                    'create_enabled' => false,
                ]
            )
//            ->add('product', 'genemu_jqueryselect2_entity', [
//                'class' => 'Marello\Bundle\ProductBundle\Entity\Product'
//            ])
            ->add('quantity', 'number', [
                'data' => 1,
            ])
            ->add('price',
                'text',
                [
                    'read_only' => true,
                ]
            )
            ->add('tax',
                'text',
                [
                    'read_only' => true,
                ]
            )
            ->add('totalPrice',
                'text',
                [
                    'read_only' => true,
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Marello\Bundle\OrderBundle\Entity\OrderItem',
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
