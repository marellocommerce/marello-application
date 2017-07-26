<?php
namespace Marello\Bundle\TaxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaxRateSelectType extends AbstractType
{
    const NAME = 'marello_tax_rate_select';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'taxrates',
                'configs'            => [
                    'placeholder' => 'marello.tax.form.select_taxrate',
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
