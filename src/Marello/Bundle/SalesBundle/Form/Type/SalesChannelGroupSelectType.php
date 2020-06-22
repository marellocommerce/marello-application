<?php

namespace Marello\Bundle\SalesBundle\Form\Type;

use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SalesChannelGroupSelectType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'group_saleschannels',
                'entity_class' => SalesChannelGroup::class,
                'grid_name' => 'marello-sales-channel-groups',
                'configs' => [
                    'placeholder' => 'marello.sales.form.choose.sales_channel_group'
                ],
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return OroEntitySelectOrCreateInlineType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix()
    {
        return 'marello_sales_saleschannel_group_select';
    }
}
