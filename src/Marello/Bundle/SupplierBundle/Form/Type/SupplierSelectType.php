<?php
namespace Marello\Bundle\SupplierBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupplierSelectType extends AbstractType
{
    const NAME = 'marello_supplier_select_form';
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'suppliers',
                'create_form_route'  => 'marello_supplier_create',
                'grid_name' => 'marello-supplier-grid',
                'configs'            => [
                    'placeholder' => 'marello.supplier.form.choose_supplier',
                    'result_template_twig' => 'MarelloSupplierBundle:Supplier:Autocomplete/result.html.twig',
                    'selection_template_twig' => 'MarelloSupplierBundle:Supplier:Autocomplete/selection.html.twig',
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'oro_entity_create_or_select_inline';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
