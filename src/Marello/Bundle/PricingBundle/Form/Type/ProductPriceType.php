<?php

namespace Marello\Bundle\PricingBundle\Form\Type;

use Marello\Bundle\PricingBundle\Entity\ProductPrice;
use Oro\Bundle\CurrencyBundle\Form\DataTransformer\MoneyValueTransformer;
use Oro\Bundle\FormBundle\Form\Type\OroDateTimeType;
use Oro\Bundle\FormBundle\Form\Type\OroMoneyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductPriceType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_product_price';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('currency', HiddenType::class, [
                'required' => true,
            ])
            ->add('startDate', OroDateTimeType::class, [
                'required' => false,
            ])
            ->add('endDate', OroDateTimeType::class, [
                'required' => false
            ]);

        if ($options['currency'] && $options['currency_symbol']) {
            $builder
                ->add('value', OroMoneyType::class, [
                    'required' => false,
                    'label' => 'marello.pricing.productprice.value.label',
                    'currency' => $options['currency'],
                    'currency_symbol' => $options['currency_symbol']
                ]);
        } else {
            $builder
                ->add('value', OroMoneyType::class, [
                    'required' => false,
                    'label' => 'marello.pricing.productprice.value.label',
                ]);
        }

        $builder->get('value')->addModelTransformer(new MoneyValueTransformer());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'        => ProductPrice::class,
            'intention'         => 'productprice',
            'single_form'       => true,
            'currency'          => null,
            'currency_symbol'   => null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
