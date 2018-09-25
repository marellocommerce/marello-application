<?php

namespace Marello\Bundle\PricingBundle\Form\Type;

use Marello\Bundle\PricingBundle\Entity\ProductChannelPrice;
use Marello\Bundle\SalesBundle\Form\Type\SalesChannelSelectType;
use Oro\Bundle\FormBundle\Form\Type\OroMoneyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductChannelPriceType extends AbstractType
{
    const NAME = 'marello_product_channel_price';

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
                'label'    => 'marello.pricing.productprice.value.label',
            ]);
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
            'excluded_channels' => []
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }
}
