<?php

namespace Marello\Bundle\OrderBundle\Form\Type;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Form\EventListener\CurrencySubscriber;
use Marello\Bundle\OrderBundle\Form\EventListener\OrderTotalsSubscriber;
use Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelRepository;
use Marello\Bundle\SalesBundle\Form\Type\SalesChannelSelectType;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\CurrencyBundle\Form\Type\PriceType;
use Oro\Bundle\FormBundle\Form\Type\OroDateTimeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    const NAME = 'marello_order_order';

    /**
     * @var SalesChannelRepository
     */
    private $salesChannelRepository;

    public function __construct(SalesChannelRepository $salesChannelRepository)
    {
        $this->salesChannelRepository = $salesChannelRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'customer',
                CustomerSelectType::NAME,
                [
                    'required' => true,
                ]
            )
            ->add(
                'salesChannel',
                SalesChannelSelectType::class,
                [
                    'autocomplete_alias' => 'active_saleschannels',
                ]
            )
            ->add(
                'discountAmount',
                TextType::class,
                [
                    'label'    => 'marello.order.discount_amount.label',
                    'required' => false,
                ]
            )
            ->add(
                'couponCode',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'locale',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'purchaseDate',
                OroDateTimeType::class,
                [
                    'required' => false,
                ]
            )
            ->add('items', OrderItemCollectionType::class);
        $this->addShippingFields($builder, $options['data']);
        $this->addBillingAddress($builder, $options);
        $this->addShippingAddress($builder, $options);

        $builder->addEventSubscriber(new OrderTotalsSubscriber());
        $builder->addEventSubscriber(new CurrencySubscriber());
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    protected function addBillingAddress(FormBuilderInterface $builder, $options)
    {
        $builder
            ->add(
                'billingAddress',
                OrderBillingAddressType::NAME,
                [
                    'label' => 'oro.order.billing_address.label',
                    'object' => $options['data'],
                    'required' => false,
                    'addressType' => 'billing',
                    'isEditEnabled' => true
                ]
            );
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    protected function addShippingAddress(FormBuilderInterface $builder, $options)
    {
        $builder
            ->add(
                'shippingAddress',
                OrderShippingAddressType::NAME,
                [
                    'label' => 'oro.order.shipping_address.label',
                    'object' => $options['data'],
                    'required' => false,
                    'addressType' => 'shipping',
                    'isEditEnabled' => true
                ]
            );
    }

    /**
     * @param FormBuilderInterface $builder
     * @param Order $order
     * @return $this
     */
    protected function addShippingFields(FormBuilderInterface $builder, Order $order)
    {
        $builder
            ->add('calculateShipping', HiddenType::class, [
                'mapped' => false
            ])
            ->add('shippingMethod', HiddenType::class)
            ->add('shippingMethodType', HiddenType::class)
            ->add('estimatedShippingCostAmount', HiddenType::class)
            ->add('overriddenShippingCostAmount', PriceType::class, [
                'required' => false,
                'validation_groups' => ['Optional'],
                'hide_currency' => true,
            ])
            ->get('overriddenShippingCostAmount')->addModelTransformer(new CallbackTransformer(
                function ($amount) use ($order) {
                    return $amount ? Price::create($amount, $order->getCurrency()) : null;
                },
                function ($price) {
                    return $price instanceof Price ? $price->getValue() : $price;
                }
            ))
        ;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Marello\Bundle\OrderBundle\Entity\Order',
            'cascade_validation'   => true
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
