<?php

namespace Marello\Bundle\SubscriptionBundle\Form\Type;

use Marello\Bundle\OrderBundle\Form\Type\CustomerSelectType;
use Marello\Bundle\SalesBundle\Form\Type\SalesChannelSelectType;
use Marello\Bundle\SubscriptionBundle\Entity\Subscription;
use Marello\Bundle\SubscriptionBundle\Form\EventListener\SubscriptionItemSubscriber;
use Marello\Bundle\SubscriptionBundle\Migrations\Data\ORM\LoadPaymentTermData;
use Marello\Bundle\SubscriptionBundle\Migrations\Data\ORM\LoadSubscriptionRenewalTypeData;
use Marello\Bundle\SubscriptionBundle\Migrations\Data\ORM\LoadSubscriptionTerminationNoticePeriodData;
use Oro\Bundle\EntityExtendBundle\Form\Type\EnumChoiceType;
use Oro\Bundle\FormBundle\Form\Type\OroDateTimeType;
use Oro\Bundle\FormBundle\Form\Type\OroChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class SubscriptionType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_subscription';

    /**
     * @var SubscriptionItemSubscriber
     */
    private $subscriptionItemSubscriber;

    /**
     * @param SubscriptionItemSubscriber $subscriptionItemSubscriber
     */
    public function __construct(SubscriptionItemSubscriber $subscriptionItemSubscriber)
    {
        $this->subscriptionItemSubscriber = $subscriptionItemSubscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'customer',
                CustomerSelectType::class,
                [
                    'required' => true,
                ]
            )
            ->add(
                'salesChannel',
                SalesChannelSelectType::class,
                [
                    'required' => true,
                    'autocomplete_alias' => 'active_saleschannels',
                ]
            )
            ->add(
                'startDate',
                OroDateTimeType::class,
                [
                    'required' => true,
                ]
            )
            ->add(
                'terminationDate',
                OroDateTimeType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'terminationNoticePeriod',
                EnumChoiceType::class,
                [
                    'enum_code' => LoadSubscriptionTerminationNoticePeriodData::ENUM_CLASS,
                    'required'  => true,
                    'label'     => 'marello.subscription.termination_notice_period.label',
                ]
            )
            ->add(
                'cancelBeforeDuration',
                OroChoiceType::class,
                [
                    'choices' => [
                        'marello.core.no.label' => false,
                        'marello.core.yes.label' => true
                    ],
                    'required' => false
                ]
            )
            ->add('item', SubscriptionProductSalesChannelAwareSelectType::class, [
                'required'       => true,
                'label'          => 'marello.subscription.item.label',
                'create_enabled' => false,
                'mapped'         => false
            ])
            ->add(
                'renewalType',
                EnumChoiceType::class,
                [
                    'enum_code' => LoadSubscriptionRenewalTypeData::ENUM_CLASS,
                    'required'  => true,
                    'label'     => 'marello.subscription.renewal_type.label',
                ]
            )
            ->add(
                'paymentMethod',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'paymentFreq',
                EnumChoiceType::class,
                [
                    'enum_code' => LoadPaymentTermData::ENUM_CLASS,
                    'required'  => true,
                    'label'     => 'marello.subscription.payment_freq.label',
                ]
            )
            ->add(
                'calculateShipping',
                HiddenType::class,
                [
                    'mapped' => false
                ]
            )
            ->add(
                'shippingMethod',
                HiddenType::class
            )
            ->add(
                'shippingMethodType',
                HiddenType::class
            );
        $this->addBillingAddress($builder, $options);
        $this->addShippingAddress($builder, $options);
        $builder->addEventSubscriber($this->subscriptionItemSubscriber);
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
                SubscriptionBillingAddressType::class,
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
                SubscriptionShippingAddressType::class,
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
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'  => Subscription::class,
            'constraints' => [new Valid()],
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
