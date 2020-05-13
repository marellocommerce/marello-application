<?php

namespace Marello\Bundle\RefundBundle\Form\Type;

use Marello\Bundle\RefundBundle\Entity\RefundItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderItemRefundType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_order_item_refund';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quantity');

        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                /** @var RefundItem $item */
                $item = $event->getData();
                $form = $event->getForm();

                if ($item === null) {
                    $form->add('refundAmount', MoneyType::class);

                    return;
                }

                $form
                    ->add('refundAmount', MoneyType::class, [
                        'empty_data' => 0,
                        'currency' => $item->getRefund()->getCurrency()
                    ]);
            });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => RefundItem::class,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
