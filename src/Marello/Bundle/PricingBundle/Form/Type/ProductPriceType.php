<?php

namespace Marello\Bundle\PricingBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductPriceType extends AbstractType
{
    const NAME = 'marello_product_price';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('currency', 'hidden', [
                'required' => true,
            ]);
        if ($options['currency'] && $options['currency_symbol']) {
            $builder
                ->add('value', 'oro_money', [
                    'required' => false,
                    'label' => 'marello.pricing.productprice.value.label',
                    'currency' => $options['currency'],
                    'currency_symbol' => $options['currency_symbol']
                ]);
        } else {
            $builder
                ->add('value', 'oro_money', [
                    'required' => false,
                    'label' => 'marello.pricing.productprice.value.label',
                ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'        => 'Marello\Bundle\PricingBundle\Entity\ProductPrice',
            'intention'         => 'productprice',
            'single_form'       => true,
            'currency'          => null,
            'currency_symbol'   => null,
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
