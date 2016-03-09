<?php

namespace Marello\Bundle\PricingBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
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
            ->add('channel', 'marello_sales_saleschannel_select', [
                  'excluded'    => $options['excluded_channels']
            ])
            ->add('currency', 'hidden', [
                'required' => true
            ])
            ->add('value', 'oro_money', [
                'required' => true,
                'label'    => 'marello.pricing.productprice.value.label',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'        => 'Marello\Bundle\PricingBundle\Entity\ProductChannelPrice',
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
}
