<?php

namespace Marello\Bundle\InventoryBundle\Form\Type;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Form\Type\OrderShippingAddressType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReshipmentType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_inventory_reshipment';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'reshipmentReason',
                ReshipmentReasonSelectType::class,
                [
                    'label' => 'marello.inventory.allocation.reshipment_reason.label',
                    'required' => false,
                    'mapped' => false,
                ]
            )
            ->add(
                'shippingAddress',
                OrderShippingAddressType::class,
                [
                    'label' => 'marello.order.shipping_address.label',
                    'object' => $options['data'],
                    'required' => false,
                    'addressType' => 'shipping',
                    'isEditEnabled' => true
                ]
            )
            ->add(
                'items',
                ReshipmentItemCollectionType::class
            );

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'assignReshipmentReason']);
    }

    public function assignReshipmentReason(FormEvent $event): void
    {
        /** @var Order $order */
        $order = $event->getData();
        $form = $event->getForm();
        $orderData = $order->getData();
        $orderData['reshipmentReason'] = $form->get('reshipmentReason')->getData();
        $order->setData($orderData);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
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
