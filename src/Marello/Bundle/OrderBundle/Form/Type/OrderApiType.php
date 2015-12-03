<?php

namespace Marello\Bundle\OrderBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderApiType extends AbstractType
{
    const NAME = 'marello_order_api';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('orderReference')
            ->add('salesChannel', 'entity', [
                'class' => 'MarelloSalesBundle:SalesChannel',
            ])
            ->add('billingAddress', 'marello_address')
            ->add('shippingAddress', 'marello_address')
            ->add('items', 'collection', [
                'type' => OrderItemApiType::NAME
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => 'Marello\Bundle\OrderBundle\Entity\Order',
            'intention'          => 'order',
            'cascade_validation' => true,
            'csrf_protection'    => false,
        ]);
    }
}
