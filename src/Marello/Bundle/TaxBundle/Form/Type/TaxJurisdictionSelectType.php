<?php

namespace Marello\Bundle\TaxBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroJquerySelect2HiddenType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaxJurisdictionSelectType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_tax_jurisdiction_select';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'taxjurisdictions',
                'configs'            => [
                    'placeholder' => 'marello.tax.form.select_taxjurisdiction',
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
