<?php

namespace Marello\Bundle\SalesBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;

class SystemGroupSalesChannelMultiselectType extends AbstractSalesChannelMultiSelectType
{
    const BLOCK_PREFIX = 'marello_system_group_sales_saleschannel_multi_select';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'autocomplete_alias' => 'system_group_saleschannels',
            'configs'            => [
                'multiple'    => true,
                'placeholder' => 'marello.sales.saleschannel.form.select_saleschannels',
                'allowClear'  => true,
                'component' => 'autocomplete-system-group-sales-channels',
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
