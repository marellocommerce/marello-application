<?php

namespace Marello\Bundle\OrderBundle\Form\Type;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
            ->add('shippingAddress', 'marello_address')
            ->add('items', 'marello_order_item_collection');

        /*
         * Takes care of setting order totals.
         */
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            /** @var Order $order */
            $order = $event->getData();

            /*
             * Reduce items to sums of prices.
             */
            $total = $tax = $grandTotal = 0;
            $order->getItems()->map(function (OrderItem $item) use (&$total, &$tax, &$grandTotal) {
                $total += $item->getPrice();
                $tax += $item->getTax();
                $grandTotal += $item->getTotalPrice();
            });

            $order
                ->setSubtotal($total)
                ->setTotalTax($tax)
                ->setGrandTotal($grandTotal);

            $event->setData($order);
        });
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
