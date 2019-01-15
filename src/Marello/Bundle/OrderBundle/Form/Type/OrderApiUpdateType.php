<?php

namespace Marello\Bundle\OrderBundle\Form\Type;

use Marello\Bundle\AddressBundle\Form\Type\AddressType;
use Marello\Bundle\OrderBundle\Entity\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class OrderApiUpdateType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_order_update_api';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('billingAddress', AddressType::class)
            ->add('shippingAddress', AddressType::class)
            ->add('paymentReference')
            ->add('invoicedAt')
            ->add('invoiceReference');
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => Order::class,
            'intention'          => 'order',
            'constraints'        => [new Valid()],
            'csrf_protection'    => false,
        ]);
    }
}
