<?php
namespace Marello\Bundle\TaxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaxCodeSelectType extends AbstractType
{
    const NAME = 'marello_tax_taxcode_select';

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
                    'result_template_twig' => 'MarelloTaxBundle:TaxCode:Autocomplete/result.html.twig',
                    'selection_template_twig' => 'MarelloTaxBundle:TaxCode:Autocomplete/selection.html.twig',
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
