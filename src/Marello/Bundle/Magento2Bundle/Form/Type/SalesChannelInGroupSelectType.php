<?php

namespace Marello\Bundle\Magento2Bundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroJquerySelect2HiddenType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SalesChannelInGroupSelectType extends AbstractType
{
    private const BLOCK_PREFIX = 'marello_magento2_sales_channel_in_group_select';

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'componentName'      => 'salesChannelInGroupSelectComponent',
                'autocomplete_alias' => 'magento2_saleschannels_in_group',
                'configs'            => [
                    'allowClear' => false,
                    'component' => 'autocomplete-magento2-sales-channel-in-group',
                    'placeholder' => 'marello.sales.saleschannel.form.select_saleschannel',
                    'result_template_twig' => 'MarelloMagento2Bundle:SalesChannel:Autocomplete/result.html.twig',
                    'selection_template_twig' => 'MarelloMagento2Bundle:SalesChannel:Autocomplete/selection.html.twig'
                ],
                'attr' => [
                    'data-role' => 'sales-channel-in-group-select'
                ]
            ]
        );
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $vars = [
            'attr' => [
                'data-page-component-name' => $options['componentName']
            ]
        ];

        $view->vars = array_replace_recursive($view->vars, $vars);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return OroJquerySelect2HiddenType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
