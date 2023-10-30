<?php

namespace Marello\Bundle\TaxBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroJquerySelect2HiddenType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaxCodeSelectType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_tax_taxcode_select';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'taxcodes',
                'configs'            => [
                    'placeholder' => 'marello.tax.form.select_taxcode',
                    'result_template_twig' => '@MarelloTax/TaxCode/Autocomplete/result.html.twig',
                    'selection_template_twig' => '@MarelloTax/TaxCode/Autocomplete/selection.html.twig',
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
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
