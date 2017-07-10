<?php

namespace Marello\Bundle\OrderBundle\Form\Type;

use Marello\Bundle\OrderBundle\Form\EventListener\OrderItemPurchasePriceSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderItemType extends AbstractType
{
    const NAME = 'marello_order_item';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('product', 'marello_product_sales_channel_aware_select', [
                'required'       => true,
                'label'          => 'marello.product.entity_label',
                'create_enabled' => false,
            ])
            ->add('quantity', 'number', [
                'data' => 1,
            ])
            ->add('price', 'text', [
                'read_only' => true,
            ])
            ->add('tax', 'text', [
                'read_only' => true,
            ])
            ->add('rowTotalExclTax', 'text', [
                'read_only' => true,
            ])
            ->add('rowTotalInclTax', 'text', [
                'read_only' => true,
            ])
        ;

        $builder->addEventSubscriber(new OrderItemPurchasePriceSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Marello\Bundle\OrderBundle\Entity\OrderItem',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
