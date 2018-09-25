<?php

namespace Marello\Bundle\OrderBundle\Form\Type;

use Marello\Bundle\AddressBundle\Form\Type\AddressType;
use Marello\Bundle\OrderBundle\Entity\Customer;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\SalesBundle\Form\Type\SalesChannelSelectApiType;
use Oro\Bundle\FormBundle\Form\DataTransformer\EntityToIdTransformer;
use Oro\Bundle\FormBundle\Form\Type\OroDateTimeType;
use Oro\Bundle\FormBundle\Form\Type\OroMoneyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class OrderApiType extends AbstractType
{
    const NAME = 'marello_order_api';

    /**
     * @var EntityToIdTransformer
     */
    protected $salesChannelTransformer;

    /**
     * @param EntityToIdTransformer $salesChannelTransformer
     */
    public function __construct(EntityToIdTransformer $salesChannelTransformer)
    {
        $this->salesChannelTransformer = $salesChannelTransformer;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('orderReference')
            ->add('salesChannel', SalesChannelSelectApiType::class, [
                'required'    => true,
                'constraints' => new NotNull(),
            ])
            ->add('subtotal', OroMoneyType::class)
            ->add('totalTax', OroMoneyType::class)
            ->add('discountAmount', OroMoneyType::class)
            ->add('currency', TextType::class)
            ->add('couponCode', TextType::class)
            ->add('grandTotal', OroMoneyType::class)
            ->add('billingAddress', AddressType::class)
            ->add('shippingAddress', AddressType::class)
            ->add('paymentMethod', TextType::class)
            ->add('paymentDetails', TextType::class)
            ->add('shippingMethod', TextType::class)
            ->add('shippingAmountInclTax', OroMoneyType::class)
            ->add('shippingAmountExclTax', OroMoneyType::class)
            ->add('purchaseDate', OroDateTimeType::class)
            ->add('items', OrderItemCollectionType::NAME, [
                'type'      => OrderItemApiType::NAME,
                'allow_add' => true,
            ]);

        $builder->get('salesChannel')->addModelTransformer($this->salesChannelTransformer);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if (is_int($data['customer'])) {
                $form->add('customer', 'entity', [
                    'class' => Customer::class
                ]);
            } else {
                $form->add('customer', CustomerApiType::NAME);
            }
        });
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
            'data_class'         => Order::class,
            'intention'          => 'order',
            'cascade_validation' => true,
            'csrf_protection'    => false,
        ]);
    }
}
