<?php

namespace Marello\Bundle\OrderBundle\Form\Type;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Form\EventListener\CurrencySubscriber;
use Marello\Bundle\OrderBundle\Form\EventListener\OrderTotalsSubscriber;
use Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelRepository;
use Marello\Bundle\SalesBundle\Form\Type\SalesChannelSelectType;
use Marello\Bundle\ShippingBundle\Form\Type\ShippingMethodSelectType;
use Symfony\Component\Form\AbstractType;
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
            ->add('items', OrderItemCollectionType::class)
            ->add('shippingMethod', ShippingMethodSelectType::class);

        $this->addAddress($builder, 'billing', $options);
        $this->addAddress($builder, 'shipping', $options);

        $builder->addEventSubscriber(new OrderTotalsSubscriber());
        $builder->addEventSubscriber(new CurrencySubscriber());
    }

    /**
     * @param FormBuilderInterface $builder
     * @param string $type
     * @param array $options
     */
    protected function addAddress(FormBuilderInterface $builder, $type, $options)
    {
        $builder
            ->add(
                    sprintf('%sAddress', $type),
                    OrderAddressType::NAME,
                    [
                        'label' => sprintf('oro.order.%s_address.label', $type),
                        'object' => $options['data'],
                        'required' => false,
                        'addressType' => $type,
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
            'data_class' => 'Marello\Bundle\OrderBundle\Entity\Order',
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
