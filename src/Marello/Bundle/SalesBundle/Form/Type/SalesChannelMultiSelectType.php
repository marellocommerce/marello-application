<?php

namespace Marello\Bundle\SalesBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;

class SalesChannelMultiSelectType extends AbstractSalesChannelMultiSelectType
{
    const BLOCK_PREFIX = 'marello_sales_saleschannel_multi_select';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'autocomplete_alias' => 'saleschannels',
            'configs'            => [
                'multiple'    => true,
                'placeholder' => 'marello.sales.saleschannel.form.select_saleschannels',
                'allowClear'  => true
            ],
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
