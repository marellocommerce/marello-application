<?php

namespace Marello\Bundle\OrderBundle\Form\Type;

use Marello\Bundle\CustomerBundle\Form\Type\CompanyAwareCustomerSelectType;
use Marello\Bundle\CustomerBundle\Form\Type\CompanySelectType;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Form\EventListener\CurrencySubscriber;
use Marello\Bundle\SalesBundle\Form\Type\SalesChannelSelectType;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\CurrencyBundle\Form\Type\PriceType;
use Oro\Bundle\FormBundle\Form\Type\OroDateTimeType;
use Oro\Bundle\LocaleBundle\Form\Type\LocalizationSelectType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class OrderType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_order_order';

    public function __construct(
        protected EventSubscriberInterface $orderTotalsSubscriber
    ) {}

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'customer',
                CompanyAwareCustomerSelectType::class,
                [
                    'required' => true
                ]
            )
            ->add(
                'company',
                CompanySelectType::class,
                [
                    'mapped' => false,
                    'required' => false,
                    'create_enabled' => false
                ]
            )
            ->add(
                'salesChannel',
                SalesChannelSelectType::class,
                [
                    'autocomplete_alias' => 'active_saleschannels'
                ]
            )
            ->add(
                'discountAmount',
                TextType::class,
                [
                    'label'    => 'marello.order.discount_amount.label',
                    'required' => false
                ]
            )
            ->add(
                'couponCode',
                TextType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'localization',
                LocalizationSelectType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'purchaseDate',
                OroDateTimeType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'deliveryDate',
                OroDateTimeType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'poNumber',
                TextType::class,
                [
                    'label' => 'marello.order.po_number.label',
                    'required' => false
                ]
            )
            ->add(
                'orderNote',
                TextareaType::class,
                [
                    'required' => false
                ]
            )
            ->add('items', OrderItemCollectionType::class);
        $this->addPaymentFields($builder);
        $this->addShippingFields($builder, $options['data']);
        $this->addBillingAddress($builder, $options);
        $this->addShippingAddress($builder, $options);

        $builder->addEventSubscriber($this->orderTotalsSubscriber);
        $builder->addEventSubscriber(new CurrencySubscriber());
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();
            $event->setData($data);
            parse_str($form->get('paymentMethodOptions')->getData(), $paymentMethodOptions);
            $data->setPaymentMethodOptions($paymentMethodOptions);
            $event->setData($data);
        });

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $data = $event->getData();
                if (!empty($data['billingAddress'])
                    && isset($data['billingAddress']['useBillingAddressAsShipping'])
                ) {
                    if ($data['billingAddress']['useBillingAddressAsShipping'] === '1') {
                        $data['shippingAddress'] = $data['billingAddress'];
                    }
                    $event->setData($data);
                }
            }
        );
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
                OrderBillingAddressType::class,
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
                OrderShippingAddressType::class,
                [
                    'label' => 'marello.order.shipping_address.label',
                    'object' => $options['data'],
                    'required' => false,
                    'addressType' => 'shipping',
                    'isEditEnabled' => true
                ]
            );
    }

    /**
     * @param FormBuilderInterface $builder
     * @return $this
     */
    protected function addPaymentFields(FormBuilderInterface $builder)
    {
        $builder
            ->add('paymentMethod', HiddenType::class)
            ->add('paymentMethodOptions', HiddenType::class, ['mapped' => false,]);

        return $this;
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
            'data_class'  => Order::class,
            'constraints' => [new Valid()]
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
