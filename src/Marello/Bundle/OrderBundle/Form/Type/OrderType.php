<?php

namespace Marello\Bundle\OrderBundle\Form\Type;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Form\Listener\OrderTotalsSubscriber;
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
            ->add(
                'salesChannel',
                'genemu_jqueryselect2_entity',
                [
                    'class' => 'MarelloSalesBundle:SalesChannel',
                ]
            )
            ->add(
                'discountAmount',
                'oro_money',
                [
                    'label'    => 'marello.order.discount_amount.label',
                    'required' => false,
                ]
            )
            ->add(
                'couponCode',
                'text',
                [
                    'required' => false,
                ]
            )
            ->add('billingAddress', 'marello_address')
            ->add('shippingAddress', 'marello_address')
            ->add('items', 'marello_order_item_collection');

        /*
         * Takes care of setting order totals.
         */
        $builder->addEventSubscriber(new OrderTotalsSubscriber());
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
