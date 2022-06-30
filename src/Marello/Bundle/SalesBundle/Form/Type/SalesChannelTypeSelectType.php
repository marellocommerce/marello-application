<?php

namespace Marello\Bundle\SalesBundle\Form\Type;

use Marello\Bundle\SalesBundle\Form\Converter\SalesChannelTypeConverter;
use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SalesChannelTypeSelectType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_sales_channel_type_select';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'entity_class'       => \Marello\Bundle\SalesBundle\Entity\SalesChannelType::class,
                'create_form_route'  => 'marello_sales_saleschanneltype_create',
                'grid_name'          => 'marello-sales-channel-types',
                'create_enabled'     => true,
                'converter'          => new SalesChannelTypeConverter(),
                'configs'            => [
                    'route_name'  => 'marello_sales_saleschanneltype_search',
                    'placeholder' => 'marello.sales.saleschanneltype.form.choose',
                    'result_template_twig' => '@MarelloSales/SalesChannelType/Autocomplete/result.html.twig',
                    'selection_template_twig' => '@MarelloSales/SalesChannelType/Autocomplete/selection.html.twig',
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return OroEntitySelectOrCreateInlineType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
