<?php

namespace Marello\Bundle\OrderBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderApiType extends OrderType
{

    const NAME = 'marello_order_api';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('items', 'collection', [
            'type' => 'marello_order_item',
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
