<?php

namespace Marello\Bundle\OrderBundle\Form\Type;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Form\EventListener\CurrencySubscriber;
use Marello\Bundle\OrderBundle\Form\EventListener\OrderTotalsSubscriber;
use Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelRepository;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
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
                'marello_sales_saleschannel_select',
                [
                    'autocomplete_alias' => 'active_saleschannels',
                ]
            )
            ->add(
                'discountAmount',
                'oro_money',
                [
                    'label'    => 'marello.order.discount_amount.label',
                    'required' => false,
                ]
            )
            ->add(
                'couponCode',
                'text',
                [
                    'required' => false,
                ]
            )
            ->add(
                'locale',
                'text',
                [
                    'required' => false,
                ]
            )
//            ->add(
//                'localization',
//                EntityType::class,
//                [
//                    'class' => 'OroLocaleBundle:Localization',
//                    'required' => false,
//                ]
//            )
            ->add('billingAddress', 'marello_address')
            ->add('shippingAddress', 'marello_address')
            ->add('items', 'marello_order_item_collection');

        /*
         * Takes care of setting order totals.
         */
        $builder->addEventSubscriber(new OrderTotalsSubscriber());
        $builder->addEventSubscriber(new CurrencySubscriber());
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
        return 'marello_order_order';
    }

    /**
     * @return SalesChannel[]
     */
    private function getSalesChannelsChoices()
    {
        $choices = [];
        foreach ($this->salesChannelRepository->getActiveChannels() as $value) {
            $choices[$value->getId()] = $value;
        }

        return $choices;
    }
}
