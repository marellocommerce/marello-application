<?php
namespace Marello\Bundle\SalesBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroJquerySelect2HiddenType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SalesChannelSelectType extends AbstractType
{
    const NAME = 'marello_sales_saleschannel_select';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'saleschannels',
                'configs'            => [
                    'placeholder' => 'marello.sales.saleschannel.form.select_saleschannel',
                    'result_template_twig' => 'MarelloSalesBundle:SalesChannel:Autocomplete/result.html.twig',
                    'selection_template_twig' => 'MarelloSalesBundle:SalesChannel:Autocomplete/selection.html.twig',
                ],
            ]
        );
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
