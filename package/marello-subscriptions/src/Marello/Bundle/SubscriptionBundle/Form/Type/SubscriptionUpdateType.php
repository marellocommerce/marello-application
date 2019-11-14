<?php

namespace Marello\Bundle\SubscriptionBundle\Form\Type;

use Marello\Bundle\SubscriptionBundle\Entity\Subscription;
use Marello\Bundle\SubscriptionBundle\Migrations\Data\ORM\LoadSubscriptionRenewalTypeData;
use Oro\Bundle\EntityExtendBundle\Form\Type\EnumChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class SubscriptionUpdateType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_subscription_update';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
