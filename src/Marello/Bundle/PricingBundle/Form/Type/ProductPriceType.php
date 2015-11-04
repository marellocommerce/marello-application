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
            ->add(
                'channel',
                'genemu_jqueryselect2_entity',
                [
                    'class' => 'MarelloSalesBundle:SalesChannel',
                ]
            )
            ->add('currency',
                'text',
                [
                    'required' => true,
                    'label'    => 'marello.productprice.currency.label',
                ]
            )
            ->add('value',
                'oro_money',
                [
                    'required' => true,
                    'label'    => 'marello.productprice.price.label',
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array('data_class' => 'Marello\\Bundle\\PricingBundle\\Entity\\ProductPrice',
                  'intention' => 'productprice',
                  'single_form' => false)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
