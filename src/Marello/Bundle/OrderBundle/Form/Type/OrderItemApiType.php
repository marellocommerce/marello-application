<?php

namespace Marello\Bundle\OrderBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderItemApiType extends AbstractType
{
    const NAME = 'marello_order_item_api';

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => 'Marello\Bundle\OrderBundle\Entity\OrderItem',
            'intention'          => 'order-item',
            'cascade_validation' => true,
            'csrf_protection'    => false,
        ]);
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('product', 'entity', [
                'class' => 'Marello\Bundle\ProductBundle\Entity\Product',
            ])->add('quantity', 'number');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
