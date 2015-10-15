<?php

namespace Marello\Bundle\OrderBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('orderNumber')
            ->add('orderReference')
            ->add('salesChannel', 'genemu_jqueryselect2_entity', [
                'class' => 'MarelloSalesBundle:SalesChannel',
            ])
            ->add('billingAddress', 'marello_address')
            ->add('shippingAddress', 'marello_address');
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Marello\Bundle\OrderBundle\Entity\Order',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'marello_order_order';
    }
}
