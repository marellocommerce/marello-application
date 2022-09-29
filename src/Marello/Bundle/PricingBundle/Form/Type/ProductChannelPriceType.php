<?php

namespace Marello\Bundle\PricingBundle\Form\Type;

use Marello\Bundle\PricingBundle\Entity\ProductChannelPrice;
use Marello\Bundle\SalesBundle\Form\Type\SalesChannelSelectType;
use Oro\Bundle\CurrencyBundle\Form\DataTransformer\MoneyValueTransformer;
use Oro\Bundle\FormBundle\Form\Type\OroDateTimeType;
use Oro\Bundle\FormBundle\Form\Type\OroMoneyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class ProductChannelPriceType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_product_channel_price';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('channel', SalesChannelSelectType::class, [
                  'excluded'    => $options['excluded_channels']
            ])
            ->add('currency', HiddenType::class, [
                'required' => true
            ])
            ->add('value', OroMoneyType::class, [
                'required' => false,
                'constraints' => $options['allowed_empty_value'] === false ? new NotNull() : null,
                'label'    => 'marello.pricing.productprice.value.label',
            ])
            ->add('startDate', OroDateTimeType::class, [
                'required' => false
            ])
            ->add('endDate', OroDateTimeType::class, [
                'required' => false
            ]);

        $builder->get('value')->addModelTransformer(new MoneyValueTransformer());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'        => ProductChannelPrice::class,
            'intention'         => 'productchannelprice',
            'single_form'       => true,
            'excluded_channels' => [],
            'allowed_empty_value' => true
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
