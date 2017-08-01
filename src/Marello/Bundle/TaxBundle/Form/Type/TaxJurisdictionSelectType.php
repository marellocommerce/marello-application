<?php
namespace Marello\Bundle\TaxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaxJurisdictionSelectType extends AbstractType
{
    const NAME = 'marello_tax_jurisdiction_select';

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
        return 'oro_jqueryselect2_hidden';
    }
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
