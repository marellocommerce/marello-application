<?php

namespace Marello\Bundle\OrderBundle\Form\Type;

use Marello\Bundle\AddressBundle\Form\Type\AddressType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderApiUpdateType extends AbstractType
{
    const NAME = 'marello_order_update_api';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('billingAddress', AddressType::NAME)
            ->add('shippingAddress', AddressType::NAME)
            ->add('paymentReference')
            ->add('invoicedAt')
            ->add('invoiceReference');
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
