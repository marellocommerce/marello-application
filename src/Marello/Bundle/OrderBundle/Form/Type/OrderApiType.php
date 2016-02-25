<?php

namespace Marello\Bundle\OrderBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\DataTransformer\EntityToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderApiType extends AbstractType
{
    const NAME = 'marello_order_api';

    /** @var EntityToIdTransformer */
    protected $salesChannelTransformer;

    /**
     * OrderApiType constructor.
     *
     * @param EntityToIdTransformer $salesChannelTransformer
     */
    public function __construct(EntityToIdTransformer $salesChannelTransformer)
    {
        $this->salesChannelTransformer = $salesChannelTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('orderReference')
            ->add('salesChannel', 'number')
            ->add('subtotal', 'oro_money')
            ->add('totalTax', 'oro_money')
            ->add('discountAmount', 'oro_money')
            ->add('currency', 'text')
            ->add('couponCode', 'text')
            ->add('grandTotal', 'oro_money')
            ->add('billingAddress', 'marello_address')
            ->add('shippingAddress', 'marello_address')
            ->add('paymentMethod', 'text')
            ->add('paymentDetails', 'text')
            ->add('shippingMethod', 'text')
            ->add('shippingAmount', 'oro_money')
            ->add('items', OrderItemCollectionType::NAME, [
                'type'         => OrderItemApiType::NAME,
                'allow_add'    => true,
            ]);

        $builder->get('salesChannel')->addModelTransformer($this->salesChannelTransformer);
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
